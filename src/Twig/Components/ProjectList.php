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

use App\Entity\Project;
use App\Entity\User;
use App\Enum\StatsPeriod;
use App\Repository\ProjectRepository;
use App\Repository\TimeEntryRepository;
use App\Stats\UsageSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use LogicException;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ProjectList extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $period = 'all';

    /**
     * @var list<array{project: Project, summary: UsageSummary}>|null
     */
    private ?array $rows = null;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return list<array{project: Project, summary: UsageSummary}>
     */
    #[ExposeInTemplate(name: 'rows')]
    public function rows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }

        [$from, $to] = $this->resolvePeriod();
        $summaries = $this->timeEntryRepository->aggregateByProjectForUser($this->currentUser(), $from, $to);

        $rows = [];
        foreach ($this->projectRepository->findBy([], ['name' => 'ASC']) as $project) {
            $key = $project->getId()?->toRfc4122();
            $rows[] = [
                'project' => $project,
                'summary' => ($key !== null && isset($summaries[$key]))
                    ? $summaries[$key]
                    : UsageSummary::empty($project->getClient()?->getCurrency()),
            ];
        }

        return $this->rows = $rows;
    }

    /**
     * @return array{tracked: CarbonInterval, earnings: array<string, float>}
     */
    #[ExposeInTemplate(name: 'totals')]
    public function totals(): array
    {
        $hours = 0.0;
        $earnings = [];

        foreach ($this->rows() as $row) {
            $summary = $row['summary'];
            $hours += $summary->totalDuration->totalHours;
            if ($summary->amount > 0 && $summary->currency !== null) {
                $earnings[$summary->currency] = ($earnings[$summary->currency] ?? 0.0) + $summary->amount;
            }
        }

        return ['tracked' => CarbonInterval::hours($hours), 'earnings' => $earnings];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[ExposeInTemplate(name: 'periodOptions')]
    public function periodOptions(): array
    {
        return array_map(
            static fn (StatsPeriod $p): array => ['value' => $p->value, 'label' => $p->label()],
            StatsPeriod::cases(),
        );
    }

    /**
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolvePeriod(): array
    {
        $period = StatsPeriod::tryFrom($this->period) ?? StatsPeriod::AllTime;
        $range = $period->range(CarbonImmutable::instance($this->clock->now()));

        return $range ?? [null, null];
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('ProjectList requires an authenticated User.');
        }

        return $user;
    }
}
