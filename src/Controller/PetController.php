<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function show(): Response
    {
        return new Response('Tutaj będzie podgląd.');
    }

    #[Route('/pet/create', name: 'pet_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return new Response('Tutaj będzie dodawanie.');
    }
}