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

namespace App\Twig\Components;

use App\Entity\TimeEntry;
use App\Entity\User;
use App\Report\GroupBy;
use App\Report\ReportFilter;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Repository\TagRepository;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ReportSummary extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $from = '';

    #[LiveProp(writable: true, url: true)]
    public string $to = '';

    #[LiveProp(writable: true, url: true)]
    public string $projectId = '';

    #[LiveProp(writable: true, url: true, onUpdated: 'onClientChanged')]
    public string $clientId = '';

    /**
     * @var list<string>
     */
    #[LiveProp(writable: true, url: true)]
    public array $tagIds = [];

    #[LiveProp(writable: true, url: true)]
    public string $billable = '';

    #[LiveProp(writable: true, url: true)]
    public string $groupBy = 'project';

    /**
     * @var list<TimeEntry>|null
     */
    private ?array $entries = null;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly ClientRepository $clientRepository,
        private readonly TagRepository $tagRepository,
        private readonly ClockInterface $clock,
        private readonly ChartBuilderInterface $chartBuilder,
    ) {
    }

    public function onClientChanged(): void
    {
        if ($this->projectId === '' || ! Ulid::isValid($this->projectId)) {
            return;
        }
        $project = $this->projectRepository->find(Ulid::fromString($this->projectId));
        if ($project === null) {
            $this->projectId = '';
            return;
        }
        if ($this->clientId !== '' && $project->getClient()?->getId()?->toRfc4122() !== $this->clientId) {
            $this->projectId = '';
        }
    }

    public function mount(): void
    {
        if ($this->from === '' || $this->to === '') {
            $now = CarbonImmutable::instance($this->clock->now());
            if ($this->from === '') {
                $this->from = $now->startOfWeek()->format('Y-m-d');
            }
            if ($this->to === '') {
                $this->to = $now->endOfWeek()->format('Y-m-d');
            }
        }
    }

    /**
     * @return array{total: CarbonInterval, billable: CarbonInterval, nonBillable: CarbonInterval, amount: float, entries: int}
     */
    #[ExposeInTemplate(name: 'totals')]
    public function totals(): array
    {
        $billableHours = 0.0;
        $nonBillableHours = 0.0;
        $amount = 0.0;

        foreach ($this->loadEntries() as $entry) {
            $duration = $entry->getDuration();
            if ($duration === null) {
                continue;
            }
            $hours = $duration->totalHours;
            if ($entry->isBillable()) {
                $billableHours += $hours;
                $rate = $entry->getProject()?->getHourlyRate();
                if ($rate !== null) {
                    $amount += $hours * $rate;
                }
            } else {
                $nonBillableHours += $hours;
            }
        }

        return [
            'total' => CarbonInterval::hours($billableHours + $nonBillableHours),
            'billable' => CarbonInterval::hours($billableHours),
            'nonBillable' => CarbonInterval::hours($nonBillableHours),
            'amount' => $amount,
            'entries' => count($this->loadEntries()),
        ];
    }

    #[ExposeInTemplate(name: 'chart')]
    public function chart(): Chart
    {
        [$start, $end] = $this->range();
        $days = max(1, (int) $start->startOfDay()->diffInDays($end->startOfDay()) + 1);
        $useWeekBuckets = $days > 62;

        /** @var array<string, array{billable: float, nonBillable: float, label: string}> $buckets */
        $buckets = [];
        $cursor = $start->startOfDay();
        while ($cursor->lessThanOrEqualTo($end)) {
            if ($useWeekBuckets) {
                $key = $cursor->startOfWeek()->format('Y-m-d');
                $label = $cursor->startOfWeek()->format('M j');
                $cursor = $cursor->addWeek();
            } else {
                $key = $cursor->format('Y-m-d');
                $label = $cursor->format('M j');
                $cursor = $cursor->addDay();
            }
            $buckets[$key] ??= ['billable' => 0.0, 'nonBillable' => 0.0, 'label' => $label];
        }

        foreach ($this->loadEntries() as $entry) {
            $duration = $entry->getDuration();
            $dateStart = $entry->getDateStart();
            if ($duration === null || $dateStart === null) {
                continue;
            }
            $key = $useWeekBuckets
                ? $dateStart->startOfWeek()->format('Y-m-d')
                : $dateStart->format('Y-m-d');
            if (! isset($buckets[$key])) {
                continue;
            }
            $bucket = &$buckets[$key];
            if ($entry->isBillable()) {
                $bucket['billable'] += $duration->totalHours;
            } else {
                $bucket['nonBillable'] += $duration->totalHours;
            }
            unset($bucket);
        }

        $labels = [];
        $billable = [];
        $nonBillable = [];
        foreach ($buckets as $bucket) {
            $labels[] = $bucket['label'];
            $billable[] = round($bucket['billable'], 2);
            $nonBillable[] = round($bucket['nonBillable'], 2);
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Billable',
                    'backgroundColor' => '#4f46e5',
                    'borderColor' => '#4f46e5',
                    'borderRadius' => 4,
                    'data' => $billable,
                    'stack' => 'time',
                ],
                [
                    'label' => 'Non-billable',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.4)',
                    'borderColor' => 'rgba(245, 158, 11, 0.4)',
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
                    'ticks' => ['color' => '#6b7280', 'font' => ['size' => 11]],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'border' => ['display' => false],
                    'grid' => ['color' => '#f3f4f6'],
                    'ticks' => ['precision' => 0, 'color' => '#9ca3af', 'font' => ['size' => 11]],
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * @return list<array{label: string, color: ?string, sub: ?string, totalHours: float, billableHours: float, amount: float, tags: list<\App\Entity\Tag>, children: list<array{label: string, totalHours: float, billableHours: float, amount: float, tags: list<\App\Entity\Tag>}>}>
     */
    #[ExposeInTemplate(name: 'groups')]
    public function groups(): array
    {
        $groupBy = $this->groupByEnum();
        /** @var array<string, array{label: string, color: ?string, sub: ?string, totalHours: float, billableHours: float, amount: float, tags: array<string, \App\Entity\Tag>, children: array<string, array{label: string, totalHours: float, billableHours: float, amount: float, tags: array<string, \App\Entity\Tag>}>}> $groups */
        $groups = [];

        foreach ($this->loadEntries() as $entry) {
            $duration = $entry->getDuration();
            if ($duration === null) {
                continue;
            }
            $hours = $duration->totalHours;
            $project = $entry->getProject();
            $rate = $project?->getHourlyRate();
            $amount = $entry->isBillable() && $rate !== null ? $hours * $rate : 0.0;

            [$key, $label, $color, $sub] = match ($groupBy) {
                GroupBy::Project => [
                    $project?->getId()?->toRfc4122() ?? '__none__',
                    $project?->getName() ?? '(No project)',
                    $project?->getColor(),
                    $project?->getClient()?->getName(),
                ],
                GroupBy::Client => [
                    $project?->getClient()?->getId()?->toRfc4122() ?? '__none__',
                    $project?->getClient()?->getName() ?? '(No client)',
                    null,
                    null,
                ],
                GroupBy::Day => [
                    $entry->getDateStart()?->format('Y-m-d') ?? '__none__',
                    $entry->getDateStart()?->format('D, M j Y') ?? '(Unknown)',
                    null,
                    null,
                ],
                GroupBy::Tag => $this->tagGroupKey($entry),
            };

            $groups[$key] ??= [
                'label' => $label,
                'color' => $color,
                'sub' => $sub,
                'totalHours' => 0.0,
                'billableHours' => 0.0,
                'amount' => 0.0,
                'tags' => [],
                'children' => [],
            ];
            $groups[$key]['totalHours'] += $hours;
            if ($entry->isBillable()) {
                $groups[$key]['billableHours'] += $hours;
            }
            $groups[$key]['amount'] += $amount;

            $description = $entry->getDescription();
            $childKey = ($description === null || $description === '') ? '(no description)' : $description;
            $groups[$key]['children'][$childKey] ??= [
                'label' => $childKey,
                'totalHours' => 0.0,
                'billableHours' => 0.0,
                'amount' => 0.0,
                'tags' => [],
            ];
            $groups[$key]['children'][$childKey]['totalHours'] += $hours;
            if ($entry->isBillable()) {
                $groups[$key]['children'][$childKey]['billableHours'] += $hours;
            }
            $groups[$key]['children'][$childKey]['amount'] += $amount;

            foreach ($entry->getTags() as $tag) {
                $tagKey = $tag->getId()?->toRfc4122() ?? $tag->getName();
                $groups[$key]['tags'][$tagKey] = $tag;
                $groups[$key]['children'][$childKey]['tags'][$tagKey] = $tag;
            }
        }

        usort($groups, static fn (array $a, array $b): int => $b['totalHours'] <=> $a['totalHours']);

        return array_map(static function (array $group): array {
            $children = array_map(static function (array $child): array {
                $child['tags'] = array_values($child['tags']);
                return $child;
            }, array_values($group['children']));
            usort($children, static fn (array $a, array $b): int => $b['totalHours'] <=> $a['totalHours']);
            $group['children'] = $children;
            $group['tags'] = array_values($group['tags']);

            return $group;
        }, $groups);
    }

    /**
     * @return array{projects: list<\App\Entity\Project>, clients: list<\App\Entity\Client>, tags: list<\App\Entity\Tag>, groupByOptions: list<array{value: string, label: string}>}
     */
    #[ExposeInTemplate(name: 'filterOptions')]
    public function filterOptions(): array
    {
        $projectCriteria = [];
        if ($this->clientId !== '' && Ulid::isValid($this->clientId)) {
            $client = $this->clientRepository->find(Ulid::fromString($this->clientId));
            if ($client !== null) {
                $projectCriteria['client'] = $client;
            }
        }

        return [
            'projects' => $this->projectRepository->findBy($projectCriteria, ['name' => 'ASC']),
            'clients' => $this->clientRepository->findBy([], ['name' => 'ASC']),
            'tags' => $this->tagRepository->findBy([], ['name' => 'ASC']),
            'groupByOptions' => array_map(
                static fn (GroupBy $g): array => ['value' => $g->value, 'label' => $g->label()],
                GroupBy::cases(),
            ),
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: ?string, 3: ?string}
     */
    private function tagGroupKey(TimeEntry $entry): array
    {
        $first = $entry->getTags()->first();
        if ($first === false) {
            return ['__none__', '(No tag)', null, null];
        }

        return [$first->getId()?->toRfc4122() ?? '__none__', $first->getName(), $first->getColor(), null];
    }

    /**
     * @return list<TimeEntry>
     */
    private function loadEntries(): array
    {
        return $this->entries ??= $this->timeEntryRepository->findForReport($this->currentUser(), $this->filter());
    }

    private function filter(): ReportFilter
    {
        return ReportFilter::fromScalars(
            from: $this->from,
            to: $this->to,
            projectId: $this->projectId !== '' ? $this->projectId : null,
            clientId: $this->clientId !== '' ? $this->clientId : null,
            tagIds: array_values(array_filter($this->tagIds, static fn (string $id): bool => $id !== '')),
            billable: $this->billable !== '' ? $this->billable : null,
        );
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function range(): array
    {
        return [
            CarbonImmutable::parse($this->from)->startOfDay(),
            CarbonImmutable::parse($this->to)->endOfDay(),
        ];
    }

    private function groupByEnum(): GroupBy
    {
        return GroupBy::tryFrom($this->groupBy) ?? GroupBy::Project;
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('ReportSummary requires an authenticated User.');
        }

        return $user;
    }
}
