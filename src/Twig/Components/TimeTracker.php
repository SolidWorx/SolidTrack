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
use App\Form\TimeTrackerType;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use LogicException;
use Override;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TimeTracker extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    /**
     * The currently running tracker (or null when nothing is being tracked).
     *
     * Held as a LiveProp so the entire component's behaviour pivots on a single
     * piece of state: actions mutate it, the template branches on it, and the
     * form is bound to it (just like editing any other Doctrine entity).
     */
    #[LiveProp]
    public ?TimeEntry $entry = null;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
        private readonly Security $security,
    ) {
    }

    #[Override]
    protected function instantiateForm(): FormInterface
    {
        // Lazy-load the active tracker here (rather than in #[PostMount]) so the
        // entity is populated BEFORE the trait's own initializeForm() PostMount
        // calls extractFormValues() — otherwise the form view is built against a
        // null entity and `formValues` snapshots empty values, which the trait's
        // submitFormOnRender then re-applies, wiping the entity on every render.
        $this->entry ??= $this->timeEntryRepository->findActiveTrackersForUser($this->currentUser());

        return $this->createForm(TimeTrackerType::class, $this->entry);
    }

    /**
     * After the trait's submitFormOnRender hook has bound the latest form values
     * onto $this->entry, flush them to the DB. This is what makes "type a
     * description while tracking → it's saved automatically" work.
     */
    #[PreReRender(priority: -100)]
    public function persistOnRender(): void
    {
        if ($this->entry?->getId() === null) {
            return;
        }

        $this->timeEntryRepository->save($this->entry);
    }

    #[LiveAction]
    public function startTracker(): void
    {
        if ($this->entry !== null) {
            return;
        }

        $this->submitForm();

        /** @var TimeEntry $entry */
        $entry = $this->getForm()->getData();
        $entry
            ->setDateStart(CarbonImmutable::instance($this->clock->now()))
            ->setStatus(TimeEntryStatus::TRACKING)
            ->setEntryType(TimeEntryType::TRACKING)
            ->setUser($this->currentUser())
        ;

        $this->timeEntryRepository->save($entry);
        $this->entry = $entry;
    }

    #[LiveAction]
    public function stopTimer(): void
    {
        if ($this->entry === null) {
            return;
        }

        // Capture any final edits sitting in formValues before we close the entry.
        $this->submitForm();

        $this->entry
            ->setDateEnd(CarbonImmutable::instance($this->clock->now()))
            ->setStatus(TimeEntryStatus::COMPLETED);

        $this->timeEntryRepository->save($this->entry);

        $this->entry = null;
        $this->resetForm();

        // The project column lives behind `data-live-ignore` (so TomSelect survives
        // re-renders mid-tracking), which means the morph won't visually clear it
        // when we null out the entry. Fire a browser event the field's Stimulus
        // controller listens for and resets TomSelect directly.
        $this->dispatchBrowserEvent('time-tracker:cleared');

        $this->emit('timer-stopped');
    }

    private function currentUser(): User
    {
        $user = $this->security->getUser();
        if (! $user instanceof User) {
            throw new LogicException('TimeTracker requires an authenticated User.');
        }

        return $user;
    }
}
