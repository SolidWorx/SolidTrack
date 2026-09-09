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
use App\Form\ManualTimeEntryType;
use App\Repository\ProjectRepository;
use App\Repository\TimeEntryRepository;
use App\Time\Duration;
use Carbon\CarbonInterval;
use LogicException;
use Override;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

/**
 * The form behind the "Add time entry" modal, for logging work that was not
 * timed live.
 *
 * Rendered *inside* the modal body rather than around it: Live rewrites the class
 * attribute of its own root element on every morph, which would strip the `show`
 * class Bootstrap puts on an open modal. Keeping the component root within the body
 * leaves open/close entirely to Bootstrap.
 */
#[AsLiveComponent]
final class ManualTimeEntry extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    /**
     * Where to send the browser after a successful save. Not writable, so Live's
     * prop checksum keeps it to the value the page rendered.
     */
    #[LiveProp]
    public ?string $returnUrl = null;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Override]
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ManualTimeEntryType::class, new TimeEntry());
    }

    /**
     * project id => client id, published to the modal so opening it from a client
     * row can narrow the project list to that client's projects.
     *
     * Sent as data rather than filtered server-side because the project field sits
     * behind `data-live-ignore`: a re-render cannot replace its options, so the
     * narrowing has to happen through TomSelect's own API.
     *
     * @return array<string, string>
     */
    #[ExposeInTemplate]
    public function projectClients(): array
    {
        $map = [];

        foreach ($this->projectRepository->findAll() as $project) {
            $clientId = $project->getClient()?->getId();

            if ($clientId !== null) {
                $map[(string) $project->getId()] = (string) $clientId;
            }
        }

        return $map;
    }

    /**
     * The period the form currently describes, for the hint under the fields.
     *
     * Null while either end is missing or the period is inverted — the form reports
     * those as validation errors, so the hint has nothing useful to add.
     */
    #[ExposeInTemplate]
    public function duration(): ?CarbonInterval
    {
        $entry = $this->getForm()->getData();

        if (! $entry instanceof TimeEntry) {
            return null;
        }

        $start = $entry->getDateStart();
        $end = $entry->getDateEnd();

        if ($start === null || $end === null || $end <= $start) {
            return null;
        }

        return $entry->getDuration();
    }

    /**
     * Redirects rather than re-rendering in place, matching UserActivity::resumeEntry()
     * and for the same reason: the project and tag cells sit behind `data-live-ignore`
     * because TomSelect owns that DOM, so a morph cannot clear them once saved. The
     * full render also refreshes whichever lists the page happens to show.
     */
    #[LiveAction]
    public function save(): RedirectResponse
    {
        $user = $this->currentUser();

        $this->submitForm();

        /** @var TimeEntry $entry */
        $entry = $this->getForm()->getData();
        $entry
            ->setUser($user)
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::MANUAL);

        $this->timeEntryRepository->save($entry);

        $this->addFlash(
            'success',
            $this->translator->trans('Added %duration% to %entry%', [
                // Same short rendering as the format_interval Twig helper, so the
                // flash reads like the durations in the list behind it.
                '%duration%' => Duration::fromHours($entry->getDuration()?->totalHours ?? 0.0)
                    ->forHumans(short: true, parts: 3),
                '%entry%' => (string) $entry,
            ]),
        );

        return $this->redirect($this->safeReturnUrl());
    }

    /**
     * The prop is checksummed, but keep the redirect to a path on this host
     * regardless — a redirect target is not somewhere to rely on a single guard.
     */
    private function safeReturnUrl(): string
    {
        $url = $this->returnUrl ?? '';

        if (! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return $this->generateUrl('dashboard');
        }

        return $url;
    }

    private function currentUser(): User
    {
        $user = $this->security->getUser();
        if (! $user instanceof User) {
            throw new LogicException('ManualTimeEntry requires an authenticated User.');
        }

        return $user;
    }
}
