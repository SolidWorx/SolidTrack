<?php

namespace App\Twig\Components;

use App\Entity\TimeEntry;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use App\Form\TimeTrackerType;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class TimeTracker extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(TimeTrackerType::class);
    }

    #[ExposeInTemplate]
    public function activeTrackers(): iterable
    {
        return $this->timeEntryRepository->findActiveTrackers();
    }

    #[LiveAction]
    public function stopTimer(#[LiveArg('id')] TimeEntry $entry, EntityManagerInterface $entityManager): void
    {
        $entry->setDateEnd(CarbonImmutable::instance($this->clock->now()))
            ->setStatus(TimeEntryStatus::COMPLETED);

        $entityManager->persist($entry);
        $entityManager->flush();
    }

    #[LiveAction]
    public function startTracker(EntityManagerInterface $entityManager): void
    {
        $this->submitForm();

        /** @var TimeEntry $entry */
        $entry = $this->getForm()->getData();

        if ($entry->getDescription() === '' || $entry->getDescription() === null) {
            $entry->setDescription('Empty Task');
        }

        $entry->setDateStart(CarbonImmutable::instance($this->clock->now()))
            ->setStatus(TimeEntryStatus::TRACKING)
            ->setEntryType(TimeEntryType::TRACKING)
        ;

        $entityManager->persist($entry);
        $entityManager->flush();

        $this->resetForm();
    }
}
