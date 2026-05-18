<?php

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
use App\Repository\TimeEntryRepository;
use Carbon\CarbonInterval;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class UserActivity extends AbstractController
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[ExposeInTemplate]
    #[LiveListener('timer-stopped')]
    public function userActivity(): iterable
    {
        $groups = [];

        /** @var User $user */
        $user = $this->getUser();
        foreach ($this->timeEntryRepository->findCompleteTrackersForUser($user) as $tracker) {
            $duration = $tracker->getDuration();
            if ($duration === null) {
                continue;
            }

            $group = match (true) {
                $tracker->getDateStart()?->isToday() => $this->translator->trans('Today'),
                $tracker->getDateStart()?->isYesterday() => $this->translator->trans('Yesterday'),
                default => $tracker->getDateStart()?->format('D d M Y'),
            };

            $groups[$group] ??= [
                'total' => CarbonInterval::create(),
                'entries' => [],
            ];

            $groups[$group]['total'] = $groups[$group]['total']->add($duration);
            $groups[$group]['entries'][] = $tracker;
        }

        return yield from $groups;
    }

    #[LiveAction]
    public function removeItem(#[LiveArg('id')] TimeEntry $entry): void
    {
        $this->timeEntryRepository->remove($entry);
        $this->emit('entry-updated');
    }

    #[LiveAction]
    public function toggleBillable(#[LiveArg('id')] TimeEntry $entry): void
    {
        $entry->setBillable(! $entry->isBillable());
        $this->timeEntryRepository->save($entry);
        $this->emit('entry-updated');
    }
}
