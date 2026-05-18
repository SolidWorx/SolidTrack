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

use App\Entity\User;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class DashboardStats extends AbstractController
{
    use DefaultActionTrait;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return array{
     *     today_total: CarbonInterval,
     *     week_total: CarbonInterval,
     *     week_billable: CarbonInterval,
     *     week_non_billable: CarbonInterval,
     *     week_earnings: array<string, float>,
     * }
     */
    #[ExposeInTemplate]
    #[LiveListener('timer-stopped')]
    #[LiveListener('entry-updated')]
    public function stats(): array
    {
        $user = $this->currentUser();
        $now = CarbonImmutable::instance($this->clock->now());
        $weekStart = $now->startOfWeek();
        $weekEnd = $now->endOfWeek();

        $todayTotal = CarbonInterval::create();
        $weekTotal = CarbonInterval::create();
        $weekBillable = CarbonInterval::create();
        $weekNonBillable = CarbonInterval::create();
        $earnings = [];

        foreach ($this->timeEntryRepository->findCompletedTrackersForUserInRange($user, $weekStart, $weekEnd) as $entry) {
            $duration = $entry->getDuration();
            if ($duration === null) {
                continue;
            }

            $weekTotal = $weekTotal->add($duration);

            if ($entry->getDateStart()?->isToday() === true) {
                $todayTotal = $todayTotal->add($duration);
            }

            if ($entry->isBillable()) {
                $weekBillable = $weekBillable->add($duration);

                $project = $entry->getProject();
                $rate = $project?->getHourlyRate();
                $currency = $project?->getClient()?->getCurrency();
                if ($rate !== null && $currency !== null) {
                    $earnings[$currency] = ($earnings[$currency] ?? 0.0) + ($duration->totalHours * $rate);
                }
            } else {
                $weekNonBillable = $weekNonBillable->add($duration);
            }
        }

        return [
            'today_total' => $todayTotal,
            'week_total' => $weekTotal,
            'week_billable' => $weekBillable,
            'week_non_billable' => $weekNonBillable,
            'week_earnings' => $earnings,
        ];
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('DashboardStats requires an authenticated User.');
        }

        return $user;
    }
}
