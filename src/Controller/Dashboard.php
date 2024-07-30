<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard', name: 'dashboard')]
final class Dashboard
{
    #[Template('dashboard/index.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
