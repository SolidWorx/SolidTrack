<?php

declare(strict_types=1);

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Project;

use App\Repository\ProjectRepository;
use SolidWorx\Platform\PlatformBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects', name: Index::class, methods: ['GET'])]
final class Index extends BaseController
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $this->projectRepository->findAll(),
        ]);
    }
}
