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

use App\Report\ReportType;
use SolidWorx\Platform\PlatformBundle\Controller\BaseController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ReportController extends BaseController
{
    /**
     * @return array{reportType: ReportType}
     */
    #[Route('/reports', name: 'app_report_index')]
    #[Template('report/index.html.twig')]
    public function __invoke(Request $request): array
    {
        $type = ReportType::tryFrom((string) $request->query->get('type', ReportType::Summary->value)) ?? ReportType::Summary;

        return [
            'reportType' => $type,
        ];
    }
}
