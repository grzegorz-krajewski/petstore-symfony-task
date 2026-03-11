<?php

namespace App\Controller;

use App\DTO\PetData;
use App\Exception\PetstoreApiException;
use App\Form\PetImageUploadType;
use App\Form\PetType;
use App\Service\PetstoreClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PetController extends AbstractController
{
    #[Route('/', name: 'pet_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pet/index.html.twig');
    }

    #[Route('/pet/show', name: 'pet_show', methods: ['GET'])]
    public function show(Request $request, PetstoreClient $petstoreClient): Response
    {
        $id = $request->query->getInt('id');

        if ($id <= 0) {
            $this->addFlash('error', 'Podano nieprawidłowe ID zwierzaka.');

            return $this->redirectToRoute('pet_index');
        }

        try {
            $pet = $petstoreClient->getPetById($id);
        } catch (PetstoreApiException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('pet_index');
        }

        if ($pet === null) {
            $this->addFlash('error', 'Nie znaleziono zwierzaka o podanym ID.');

            return $this->redirectToRoute('pet_index');
        }

        $uploadForm = $this->createForm(PetImageUploadType::class);

        return $this->render('pet/show.html.twig', [
            'pet' => $pet,
            'uploadForm' => $uploadForm->createView(),
        ]);
    }

    #[Route('/pet/create', name: 'pet_create', methods: ['GET', 'POST'])]
    public function create(Request $request, PetstoreClient $petstoreClient): Response
    {
        $petData = new PetData();
        $form = $this->createForm(PetType::class, $petData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $createdPet = $petstoreClient->createPet($petData->toArray());
                $petId = (int) ($createdPet['id'] ?? $petData->id);

                $uploadedCount = $this->handleEmbeddedImagesUpload($form, $petId, $petstoreClient);

                $this->addFlash('success', 'Zwierzak został dodany.');
                if ($uploadedCount > 0) {
                    $this->addFlash('success', sprintf('Przesłano %d obraz(ów) do API.', $uploadedCount));
                }
            } catch (PetstoreApiException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('pet/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            return $this->redirectToRoute('pet_show', [
                'id' => $petId,
            ]);
        }

        return $this->render('pet/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pet/edit/{id}', name: 'pet_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, PetstoreClient $petstoreClient): Response
    {
        try {
            $pet = $petstoreClient->getPetById($id);
        } catch (PetstoreApiException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('pet_index');
        }

        if ($pet === null) {
            $this->addFlash('error', 'Nie znaleziono zwierzaka do edycji.');

            return $this->redirectToRoute('pet_index');
        }

        $petData = PetData::fromArray($pet);

        $form = $this->createForm(PetType::class, $petData, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petData->id = $id;

            try {
                $updatedPet = $petstoreClient->updatePet($petData->toArray());
                $petId = (int) ($updatedPet['id'] ?? $petData->id);

                $uploadedCount = $this->handleEmbeddedImagesUpload($form, $petId, $petstoreClient);

                $this->addFlash('success', 'Zwierzak został zaktualizowany.');
                if ($uploadedCount > 0) {
                    $this->addFlash('success', sprintf('Przesłano %d obraz(ów) do API.', $uploadedCount));
                }
            } catch (PetstoreApiException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('pet/edit.html.twig', [
                    'form' => $form->createView(),
                    'petId' => $id,
                ]);
            }

            return $this->redirectToRoute('pet_show', [
                'id' => $petId,
            ]);
        }

        return $this->render('pet/edit.html.twig', [
            'form' => $form->createView(),
            'petId' => $id,
        ]);
    }

    #[Route('/pet/delete/{id}', name: 'pet_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, PetstoreClient $petstoreClient): Response
    {
        if (!$this->isCsrfTokenValid('delete_pet_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Nieprawidłowy token bezpieczeństwa.');

            return $this->redirectToRoute('pet_index');
        }

        try {
            $petstoreClient->deletePet($id);
        } catch (PetstoreApiException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('pet_index');
        }

        $this->addFlash('success', 'Zwierzak został usunięty.');

        return $this->redirectToRoute('pet_index');
    }

    #[Route('/pet/{id}/upload-image', name: 'pet_upload_image', methods: ['POST'])]
    public function uploadImage(int $id, Request $request, PetstoreClient $petstoreClient): Response
    {
        $form = $this->createForm(PetImageUploadType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Nie udało się przesłać obrazów.');

            return $this->redirectToRoute('pet_show', [
                'id' => $id,
            ]);
        }

        $data = $form->getData();
        $images = $data['images'] ?? [];

        if (!is_array($images) || $images === []) {
            $this->addFlash('error', 'Nie wybrano plików.');

            return $this->redirectToRoute('pet_show', [
                'id' => $id,
            ]);
        }

        $uploadedCount = 0;

        try {
            foreach ($images as $image) {
                if (!$image instanceof UploadedFile) {
                    continue;
                }

                $petstoreClient->uploadPetImage(
                    $id,
                    $image,
                    $data['additionalMetadata'] ?? null
                );

                $uploadedCount++;
            }
        } catch (PetstoreApiException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('pet_show', [
                'id' => $id,
            ]);
        }

        if ($uploadedCount === 0) {
            $this->addFlash('error', 'Nie udało się przesłać żadnego obrazu.');
        } else {
            $this->addFlash('success', sprintf('Przesłano %d obraz(ów) do API.', $uploadedCount));
        }

        return $this->redirectToRoute('pet_show', [
            'id' => $id,
        ]);
    }

    private function handleEmbeddedImagesUpload(FormInterface $form, int $petId, PetstoreClient $petstoreClient): int
    {
        $imageUploadData = $form->get('imageUpload')->getData();

        if (!is_array($imageUploadData)) {
            return 0;
        }

        $images = $imageUploadData['images'] ?? [];

        if (!is_array($images) || $images === []) {
            return 0;
        }

        $uploadedCount = 0;

        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }

            $petstoreClient->uploadPetImage(
                $petId,
                $image,
                $imageUploadData['additionalMetadata'] ?? null
            );

            $uploadedCount++;
        }

        return $uploadedCount;
    }
}