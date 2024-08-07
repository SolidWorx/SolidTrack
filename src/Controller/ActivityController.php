<?php

namespace App\Controller;

use App\Repository\TimeEntryRepository;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function iterator_to_array;

#[Route('/activities', name: 'app_activity_index')]
final class ActivityController extends AbstractController
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly TimeEntryRepository   $repository,
    ) {}

    #[Template('activity/index.html.twig')]
    public function __invoke(): array
    {
        $lastWeek = CarbonPeriod::create(Carbon::now()->subDays(6), CarbonInterval::day(), 7);

        $groups = array_fill_keys(
            array_map(static fn(CarbonInterface $date) => $date->format('D d M Y'), iterator_to_array($lastWeek)),
            null,
        );

        foreach ($this->repository->findCompleteTrackersForUser($this->getUser()) as $entry) {
            $group = $entry->getDateStart()?->format('D d M Y');

            $groups[$group] ??= CarbonInterval::create();

            $groups[$group] = $groups[$group]?->add($entry->getDuration());
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => array_keys($groups),
            'datasets' => [
                [
                    'label' => 'Total Hours',
                    //'backgroundColor' => 'rgb(255, 99, 132, .4)',
                    //'borderColor' => 'rgb(255, 99, 132)',
                    'data' => array_map(static fn(?CarbonInterval $interval) => $interval?->totalHours ?? 0, $groups),
                    //'labels' => ['1', '2', '3', '4', '5', '6', '7'],
                    'tension' => 0.4,
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'ticks' => [
                        // Include a dollar sign in the ticks
                        'callback' => "function(value, index, ticks) {
                            return '$' + value;
            
                        }",
                    ],
                ],
            ],
        ]);

        return [
            'chart' => $chart,
        ];
    }
}
