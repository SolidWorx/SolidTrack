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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class BillableSplit extends AbstractController
{
    use DefaultActionTrait;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array{billable: CarbonInterval, nonBillable: CarbonInterval, billablePct: int}
     */
    #[ExposeInTemplate]
    #[LiveListener('timer-stopped')]
    #[LiveListener('entry-updated')]
    public function split(): array
    {
        $user = $this->currentUser();
        $now = CarbonImmutable::instance($this->clock->now());

        $billable = CarbonInterval::create();
        $nonBillable = CarbonInterval::create();

        foreach ($this->timeEntryRepository->findCompletedTrackersForUserInRange($user, $now->startOfWeek(), $now->endOfWeek()) as $entry) {
            $duration = $entry->getDuration();
            if ($duration === null) {
                continue;
            }

            if ($entry->isBillable()) {
                $billable = $billable->add($duration);
            } else {
                $nonBillable = $nonBillable->add($duration);
            }
        }

        $totalSeconds = $billable->totalSeconds + $nonBillable->totalSeconds;
        $pct = $totalSeconds > 0 ? (int) round($billable->totalSeconds / $totalSeconds * 100) : 0;

        return [
            'billable' => $billable,
            'nonBillable' => $nonBillable,
            'billablePct' => $pct,
        ];
    }

    private function currentUser(): User
    {
        $user = $this->security->getUser();
        if (! $user instanceof User) {
            throw new LogicException('BillableSplit requires an authenticated User.');
        }

        return $user;
    }
}
