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
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return iterable<string, array{total: CarbonInterval, entries: list<TimeEntry>}>
     */
    #[ExposeInTemplate]
    #[LiveListener(eventName: 'timer-stopped')]
    public function userActivity(): iterable
    {
        $groups = [];

        $user = $this->currentUser();
        foreach ($this->timeEntryRepository->findCompleteTrackersForUser($user) as $tracker) {
            $duration = $tracker->getDuration();
            $dateStart = $tracker->getDateStart();
            if ($duration === null || $dateStart === null) {
                continue;
            }

            $group = match (true) {
                $dateStart->isToday() => $this->translator->trans('Today'),
                $dateStart->isYesterday() => $this->translator->trans('Yesterday'),
                default => $dateStart->format('D d M Y'),
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
    public function removeItem(#[LiveArg(name: 'id')] TimeEntry $entry): void
    {
        $this->assertOwnedByCurrentUser($entry);

        $this->timeEntryRepository->remove($entry);
        $this->emit('entry-updated');
    }

    #[LiveAction]
    public function toggleBillable(#[LiveArg(name: 'id')] TimeEntry $entry): void
    {
        $this->assertOwnedByCurrentUser($entry);

        $entry->setBillable(! $entry->isBillable());
        $this->timeEntryRepository->save($entry);
        $this->emit('entry-updated');
    }

    /**
     * Start a new timer carrying over the project, description, tags and billable
     * flag of an entry already in the list.
     *
     * Only one tracker may run per user (see TimeEntryRepository::findActiveTrackersForUser),
     * so a running timer is stopped and closed off first rather than the click being
     * refused — refusing would leave the most-used control in the list doing nothing
     * whenever a timer happens to be running.
     *
     * Returns a redirect rather than re-rendering in place: the tracker bar's project
     * and tag fields sit behind `data-live-ignore` (TomSelect owns that DOM), so a
     * live morph cannot show the carried-over project. A full render can.
     */
    #[LiveAction]
    public function resumeEntry(#[LiveArg(name: 'id')] TimeEntry $entry): RedirectResponse
    {
        $this->assertOwnedByCurrentUser($entry);

        $user = $this->currentUser();
        $now = CarbonImmutable::instance($this->clock->now());

        $running = $this->timeEntryRepository->findActiveTrackersForUser($user);
        if ($running instanceof TimeEntry) {
            $running
                ->setDateEnd($now)
                ->setStatus(TimeEntryStatus::COMPLETED);

            $this->timeEntryRepository->save($running);
        }

        $resumed = new TimeEntry();
        $resumed
            ->setUser($user)
            ->setProject($entry->getProject())
            ->setDescription($entry->getDescription())
            ->setBillable($entry->isBillable())
            ->setDateStart($now)
            ->setStatus(TimeEntryStatus::TRACKING)
            ->setEntryType(TimeEntryType::TRACKING);

        foreach ($entry->getTags() as $tag) {
            $resumed->addTag($tag);
        }

        $this->timeEntryRepository->save($resumed);

        // Rendered by the layout's flash block as a Tabler alert, which is what
        // announces the state change to screen readers after the redirect.
        $this->addFlash(
            $running instanceof TimeEntry ? 'info' : 'success',
            $running instanceof TimeEntry
                ? $this->translator->trans('Previous timer stopped. Now tracking: %entry%', ['%entry%' => (string) $resumed])
                : $this->translator->trans('Now tracking: %entry%', ['%entry%' => (string) $resumed]),
        );

        return $this->redirectToRoute('dashboard');
    }

    private function assertOwnedByCurrentUser(TimeEntry $entry): void
    {
        if ($entry->getUser()?->getId()->equals($this->currentUser()->getId()) !== true) {
            throw $this->createAccessDeniedException('This time entry belongs to another user.');
        }
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('UserActivity requires an authenticated User.');
        }

        return $user;
    }
}
