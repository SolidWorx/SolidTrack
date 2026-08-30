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

use App\Chart\BillableChartFactory;
use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Report\ReportFilter;
use App\Repository\ProjectRepository;
use App\Repository\TagRepository;
use App\Repository\TimeEntryRepository;
use App\Time\Duration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ProjectTimeEntries extends AbstractController
{
    use DefaultActionTrait;

    public const PER_PAGE = 50;

    #[LiveProp]
    public string $projectId = '';

    #[LiveProp(writable: true, url: true)]
    public string $from = '';

    #[LiveProp(writable: true, url: true)]
    public string $to = '';

    /**
     * @var list<string>
     */
    #[LiveProp(writable: true, url: true)]
    public array $tagIds = [];

    #[LiveProp(writable: true, url: true)]
    public string $billable = '';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    /**
     * @var list<TimeEntry>|null
     */
    private ?array $entries = null;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly TagRepository $tagRepository,
        private readonly ClockInterface $clock,
        private readonly BillableChartFactory $billableChartFactory,
    ) {
    }

    public function mount(): void
    {
        if ($this->from !== '' && $this->to !== '') {
            return;
        }

        $now = CarbonImmutable::instance($this->clock->now());

        if ($this->to === '') {
            $this->to = $now->format('Y-m-d');
        }

        if ($this->from === '') {
            $project = $this->resolveProject();
            $earliest = $project !== null
                ? $this->timeEntryRepository->earliestEntryDateForProjectAndUser($this->currentUser(), $project)
                : null;
            $this->from = ($earliest ?? $now->startOfMonth())->format('Y-m-d');
        }
    }

    #[LiveAction]
    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    #[LiveAction]
    public function nextPage(): void
    {
        ++$this->page;
    }

    /**
     * @return list<TimeEntry>
     */
    #[ExposeInTemplate(name: 'entries')]
    public function entries(): array
    {
        return $this->timeEntryRepository->findForReport(
            $this->currentUser(),
            $this->filter(),
            max(1, $this->page),
            self::PER_PAGE,
        );
    }

    /**
     * @return array{total: CarbonInterval, billable: CarbonInterval, nonBillable: CarbonInterval, amount: float, count: int, pages: int}
     */
    #[ExposeInTemplate(name: 'totals')]
    public function totals(): array
    {
        $billable = 0.0;
        $nonBillable = 0.0;
        $amount = 0.0;

        foreach ($this->loadEntries() as $entry) {
            $duration = $entry->getDuration();
            if ($duration === null) {
                continue;
            }
            $hours = $duration->totalHours;
            if ($entry->isBillable()) {
                $billable += $hours;
                $rate = $entry->getProject()?->getHourlyRate();
                if ($rate !== null) {
                    $amount += $hours * $rate;
                }
            } else {
                $nonBillable += $hours;
            }
        }

        $count = $this->timeEntryRepository->countForReport($this->currentUser(), $this->filter());

        return [
            'total' => Duration::fromHours($billable + $nonBillable),
            'billable' => Duration::fromHours($billable),
            'nonBillable' => Duration::fromHours($nonBillable),
            'amount' => $amount,
            'count' => $count,
            'pages' => max(1, (int) ceil($count / self::PER_PAGE)),
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

        return $this->billableChartFactory->create($labels, $billable, $nonBillable);
    }

    /**
     * @return array{tags: list<\App\Entity\Tag>}
     */
    #[ExposeInTemplate(name: 'filterOptions')]
    public function filterOptions(): array
    {
        return [
            'tags' => $this->tagRepository->findBy([], ['name' => 'ASC']),
        ];
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
            clientId: null,
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

    private function resolveProject(): ?Project
    {
        if ($this->projectId === '' || ! Ulid::isValid($this->projectId)) {
            return null;
        }

        return $this->projectRepository->find(Ulid::fromString($this->projectId));
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('ProjectTimeEntries requires an authenticated User.');
        }

        return $user;
    }
}
