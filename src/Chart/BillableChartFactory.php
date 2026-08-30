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

namespace App\Chart;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * The stacked billable / non-billable bar chart, shared by the dashboard, the
 * reports and the project page.
 *
 * Chart.js paints to a canvas and cannot resolve CSS custom properties, so the
 * palette is duplicated here as literals. It mirrors `assets/scss/_variables.scss`
 * and Tabler's grey ramp; keeping it in one class is what stops the three
 * components drifting apart.
 */
final readonly class BillableChartFactory
{
    public const string BILLABLE_COLOR = '#4f46e5';

    /**
     * Softened so the stacked segments stay distinguishable.
     */
    public const string NON_BILLABLE_COLOR = 'rgba(245, 158, 11, 0.4)';

    private const string AXIS_LABEL_COLOR = '#6b7280';

    private const string AXIS_TICK_COLOR = '#9ca3af';

    private const string GRID_COLOR = '#f3f4f6';

    public function __construct(
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    /**
     * For an HTML legend beside one of these charts: it has to be painted with the
     * canvas values, not a `bg-*` utility, which would render the non-billable
     * swatch fully saturated while its bars are drawn at 40% alpha.
     *
     * @return array{billable: string, nonBillable: string}
     */
    public static function legendColors(): array
    {
        return [
            'billable' => self::BILLABLE_COLOR,
            'nonBillable' => self::NON_BILLABLE_COLOR,
        ];
    }

    /**
     * @param list<string> $labels      one label per bar
     * @param list<float>  $billable    hours, aligned with $labels
     * @param list<float>  $nonBillable hours, aligned with $labels
     */
    public function create(array $labels, array $billable, array $nonBillable): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Billable',
                    'backgroundColor' => self::BILLABLE_COLOR,
                    'borderColor' => self::BILLABLE_COLOR,
                    'borderRadius' => 4,
                    'data' => $billable,
                    'stack' => 'time',
                ],
                [
                    'label' => 'Non-billable',
                    'backgroundColor' => self::NON_BILLABLE_COLOR,
                    'borderColor' => self::NON_BILLABLE_COLOR,
                    'borderRadius' => 4,
                    'data' => $nonBillable,
                    'stack' => 'time',
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['mode' => 'index', 'intersect' => false],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'grid' => ['display' => false],
                    'ticks' => ['color' => self::AXIS_LABEL_COLOR, 'font' => ['size' => 11]],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'border' => ['display' => false],
                    'grid' => ['color' => self::GRID_COLOR],
                    'ticks' => ['precision' => 0, 'color' => self::AXIS_TICK_COLOR, 'font' => ['size' => 11]],
                ],
            ],
        ]);

        return $chart;
    }
}
