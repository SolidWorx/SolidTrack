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
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Repository\TimeEntryRepository;
use App\Time\Duration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class WeeklyChart extends AbstractController
{
    use DefaultActionTrait;

    /**
     * Offset in weeks from the current week. 0 = this week, -1 = last week, +1 = next week.
     */
    #[LiveProp(writable: true)]
    public int $weekOffset = 0;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
        private readonly BillableChartFactory $billableChartFactory,
    ) {
    }

    #[LiveAction]
    public function previousWeek(): void
    {
        --$this->weekOffset;
    }

    #[LiveAction]
    public function nextWeek(): void
    {
        ++$this->weekOffset;
    }

    #[LiveAction]
    public function currentWeek(): void
    {
        $this->weekOffset = 0;
    }

    /**
     * @return array{billable: string, nonBillable: string}
     */
    #[ExposeInTemplate(name: 'legendColors')]
    public function legendColors(): array
    {
        return BillableChartFactory::legendColors();
    }

    #[ExposeInTemplate(name: 'rangeLabel')]
    public function rangeLabel(): string
    {
        [$start, $end] = $this->range();

        if ($start->month === $end->month) {
            return $start->format('M j') . ' – ' . $end->format('j');
        }

        return $start->format('M j') . ' – ' . $end->format('M j');
    }

    /**
     * @return array{total: CarbonInterval, billable: CarbonInterval}
     */
    #[ExposeInTemplate(name: 'totals')]
    #[LiveListener(eventName: 'timer-stopped')]
    #[LiveListener(eventName: 'entry-updated')]
    public function totals(): array
    {
        $billableHours = 0.0;
        $nonBillableHours = 0.0;

        foreach ($this->loadEntries() as $entry) {
            $duration = $entry->getDuration();
            if ($duration === null) {
                continue;
            }

            if ($entry->isBillable()) {
                $billableHours += $duration->totalHours;
            } else {
                $nonBillableHours += $duration->totalHours;
            }
        }

        return [
            'total' => Duration::fromHours($billableHours + $nonBillableHours),
            'billable' => Duration::fromHours($billableHours),
        ];
    }

    #[ExposeInTemplate(name: 'chart')]
    public function chart(): Chart
    {
        [$start] = $this->range();

        $perDay = array_fill(0, 7, ['billable' => 0.0, 'nonBillable' => 0.0]);

        foreach ($this->loadEntries() as $entry) {
            $duration = $entry->getDuration();
            $dateStart = $entry->getDateStart();
            if ($duration === null || $dateStart === null) {
                continue;
            }

            $dayIndex = (int) $start->startOfDay()
                ->diffInDays($dateStart->startOfDay(), absolute: false);
            if ($dayIndex < 0 || $dayIndex > 6) {
                continue;
            }

            if ($entry->isBillable()) {
                $perDay[$dayIndex]['billable'] += $duration->totalHours;
            } else {
                $perDay[$dayIndex]['nonBillable'] += $duration->totalHours;
            }
        }

        $labels = [];
        $billable = [];
        $nonBillable = [];
        for ($i = 0; $i < 7; ++$i) {
            $labels[] = $start->copy()->addDays($i)->format('D');
            $billable[] = round($perDay[$i]['billable'], 2);
            $nonBillable[] = round($perDay[$i]['nonBillable'], 2);
        }

        return $this->billableChartFactory->create($labels, $billable, $nonBillable);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function range(): array
    {
        $base = CarbonImmutable::instance($this->clock->now())->addWeeks($this->weekOffset);

        return [$base->startOfWeek(), $base->endOfWeek()];
    }

    /**
     * @return iterable<TimeEntry>
     */
    private function loadEntries(): iterable
    {
        [$start, $end] = $this->range();

        return $this->timeEntryRepository->findCompletedTrackersForUserInRange(
            $this->currentUser(),
            $start,
            $end,
        );
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('WeeklyChart requires an authenticated User.');
        }

        return $user;
    }
}
