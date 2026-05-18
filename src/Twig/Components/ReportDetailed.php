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
use App\Report\ReportFilter;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Repository\TagRepository;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ReportDetailed extends AbstractController
{
    use DefaultActionTrait;

    public const PER_PAGE = 50;

    #[LiveProp(writable: true, url: true)]
    public string $from = '';

    #[LiveProp(writable: true, url: true)]
    public string $to = '';

    #[LiveProp(writable: true, url: true)]
    public string $projectId = '';

    #[LiveProp(writable: true, url: true, onUpdated: 'onClientChanged')]
    public string $clientId = '';

    /**
     * @var list<string>
     */
    #[LiveProp(writable: true, url: true)]
    public array $tagIds = [];

    #[LiveProp(writable: true, url: true)]
    public string $billable = '';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly ClientRepository $clientRepository,
        private readonly TagRepository $tagRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    public function onClientChanged(): void
    {
        if ($this->projectId === '' || ! Ulid::isValid($this->projectId)) {
            return;
        }
        $project = $this->projectRepository->find(Ulid::fromString($this->projectId));
        if ($project === null) {
            $this->projectId = '';
            return;
        }
        if ($this->clientId !== '' && $project->getClient()?->getId()?->toRfc4122() !== $this->clientId) {
            $this->projectId = '';
        }
        $this->page = 1;
    }

    public function mount(): void
    {
        if ($this->from === '' || $this->to === '') {
            $now = CarbonImmutable::instance($this->clock->now());
            if ($this->from === '') {
                $this->from = $now->startOfWeek()->format('Y-m-d');
            }
            if ($this->to === '') {
                $this->to = $now->endOfWeek()->format('Y-m-d');
            }
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
     * @return array{total: CarbonInterval, billable: CarbonInterval, amount: float, count: int, pages: int}
     */
    #[ExposeInTemplate(name: 'totals')]
    public function totals(): array
    {
        $filter = $this->filter();
        $user = $this->currentUser();
        $total = 0.0;
        $billable = 0.0;
        $amount = 0.0;

        foreach ($this->timeEntryRepository->findForReport($user, $filter) as $entry) {
            $duration = $entry->getDuration();
            if ($duration === null) {
                continue;
            }
            $hours = $duration->totalHours;
            $total += $hours;
            if ($entry->isBillable()) {
                $billable += $hours;
                $rate = $entry->getProject()?->getHourlyRate();
                if ($rate !== null) {
                    $amount += $hours * $rate;
                }
            }
        }

        $count = $this->timeEntryRepository->countForReport($user, $filter);

        return [
            'total' => CarbonInterval::hours($total),
            'billable' => CarbonInterval::hours($billable),
            'amount' => $amount,
            'count' => $count,
            'pages' => max(1, (int) ceil($count / self::PER_PAGE)),
        ];
    }

    /**
     * @return array{projects: list<\App\Entity\Project>, clients: list<\App\Entity\Client>, tags: list<\App\Entity\Tag>, groupByOptions: list<array{value: string, label: string}>}
     */
    #[ExposeInTemplate(name: 'filterOptions')]
    public function filterOptions(): array
    {
        $projectCriteria = [];
        if ($this->clientId !== '' && Ulid::isValid($this->clientId)) {
            $client = $this->clientRepository->find(Ulid::fromString($this->clientId));
            if ($client !== null) {
                $projectCriteria['client'] = $client;
            }
        }

        return [
            'projects' => $this->projectRepository->findBy($projectCriteria, ['name' => 'ASC']),
            'clients' => $this->clientRepository->findBy([], ['name' => 'ASC']),
            'tags' => $this->tagRepository->findBy([], ['name' => 'ASC']),
            'groupByOptions' => [],
        ];
    }

    private function filter(): ReportFilter
    {
        return ReportFilter::fromScalars(
            from: $this->from,
            to: $this->to,
            projectId: $this->projectId !== '' ? $this->projectId : null,
            clientId: $this->clientId !== '' ? $this->clientId : null,
            tagIds: array_values(array_filter($this->tagIds, static fn (string $id): bool => $id !== '')),
            billable: $this->billable !== '' ? $this->billable : null,
        );
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('ReportDetailed requires an authenticated User.');
        }

        return $user;
    }
}
