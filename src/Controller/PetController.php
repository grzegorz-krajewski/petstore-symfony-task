<?php

namespace App\Controller;

use App\DTO\PetData;
use App\Exception\PetstoreApiException;
use App\Form\PetType;
use App\Service\PetstoreClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $this->addFlash('error', 'Podano nieprawidłowe ID.');

            return $this->redirectToRoute('pet_index');
        }

        try {
            $pet = $petstoreClient->getPetById($id);
        } catch (PetstoreApiException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('pet_index');
        }

        if ($pet === null) {
            $this->addFlash('error', 'Nie znaleziono ID.');

            return $this->redirectToRoute('pet_index');
        }

        return $this->render('pet/show.html.twig', [
            'pet' => $pet,
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
            } catch (PetstoreApiException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('pet/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Zwierzak został dodany.');

            return $this->redirectToRoute('pet_show', [
                'id' => $createdPet['id'] ?? $petData->id,
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
        $form = $this->createForm(PetType::class, $petData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updatedPet = $petstoreClient->updatePet($petData->toArray());
            } catch (PetstoreApiException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('pet/edit.html.twig', [
                    'form' => $form->createView(),
                    'petId' => $id,
                ]);
            }

            $this->addFlash('success', 'Zwierzak został zaktualizowany.');

            return $this->redirectToRoute('pet_show', [
                'id' => $updatedPet['id'] ?? $petData->id,
            ]);
        }

        return $this->render('pet/edit.html.twig', [
            'form' => $form->createView(),
            'petId' => $id,
        ]);
    }

    #[Route('/pet/delete/{id}', name: 'pet_delete', methods: ['POST'])]
    public function delete(int $id, PetstoreClient $petstoreClient): Response
    {
        try {
            $petstoreClient->deletePet($id);
        } catch (PetstoreApiException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('pet_index');
        }

        $this->addFlash('success', 'Zwierzak został usunięty.');

        return $this->redirectToRoute('pet_index');
    }
}