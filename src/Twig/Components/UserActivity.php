<?php

namespace App\Twig\Components;

use App\Repository\TimeEntryRepository;
use Carbon\CarbonInterval;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class UserActivity extends AbstractController
{
    use DefaultActionTrait;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly TranslatorInterface $translator,
    ) {}

    #[ExposeInTemplate]
    public function userActivity(): iterable
    {
        $groups = [];

        foreach ($this->timeEntryRepository->findCompleteTrackers() as $tracker) {
            $group = match (true) {
                $tracker->getDateStart()?->isToday() => $this->translator->trans('Today'),
                $tracker->getDateStart()?->isYesterday() => $this->translator->trans('Yesterday'),
                default => $tracker->getDateStart()?->format('D d M Y'),
            };

            $groups[$group] ??= [
                'total' => CarbonInterval::create(),
                'entries' => [],
            ];

            $groups[$group]['total'] = $groups[$group]['total']->add($tracker->getDuration());
            $groups[$group]['entries'][] = $tracker;
        }

        return yield from $groups;
    }
}
