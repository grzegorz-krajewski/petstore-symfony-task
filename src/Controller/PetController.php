<?php

namespace App\Controller;

use App\DTO\PetData;
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

        $pet = $petstoreClient->getPetById($id);

        if ($pet === null) {
            $this->addFlash('error', 'Nie znaleziono.');

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
            $createdPet = $petstoreClient->createPet([
                'id' => $petData->id,
                'name' => $petData->name,
                'status' => $petData->status,
            ]);

            $this->addFlash('success', 'Zwierzak został dodany.');

            return $this->redirectToRoute('pet_show', [
                'id' => $createdPet['id'] ?? $petData->id,
            ]);
        }

        return $this->render('pet/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}