<?php

namespace App\Controller\Project;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects', name: Index::class, methods: ['GET'])]
final class Index extends AbstractController
{
    public function __construct(private readonly ProjectRepository $projectRepository)
    {
    }

    public function __invoke(): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $this->projectRepository->findAll(),
        ]);
    }
}
