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

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard', name: 'dashboard')]
final class Dashboard
{
    /**
     * @return array<string, mixed>
     */
    #[Template('dashboard/index.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
