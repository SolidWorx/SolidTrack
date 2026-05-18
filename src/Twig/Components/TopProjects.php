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
final class TopProjects extends AbstractController
{
    use DefaultActionTrait;

    private const LIMIT = 5;

    public function __construct(
        private readonly TimeEntryRepository $timeEntryRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return array{
     *     projects: list<array{project: Project, duration: CarbonInterval, percent: float}>,
     *     total: CarbonInterval,
     * }
     */
    #[ExposeInTemplate]
    #[LiveListener('timer-stopped')]
    #[LiveListener('entry-updated')]
    public function topProjects(): array
    {
        $user = $this->currentUser();
        $now = CarbonImmutable::instance($this->clock->now());
        $start = $now->startOfWeek();
        $end = $now->endOfWeek();

        /** @var array<string, array{project: Project, hours: float}> $byProject */
        $byProject = [];

        foreach ($this->timeEntryRepository->findCompletedTrackersForUserInRange($user, $start, $end) as $entry) {
            $project = $entry->getProject();
            $duration = $entry->getDuration();
            if ($project === null || $duration === null) {
                continue;
            }

            $key = (string) $project->getId();
            $byProject[$key] ??= ['project' => $project, 'hours' => 0.0];
            $byProject[$key]['hours'] += $duration->totalHours;
        }

        usort($byProject, static fn (array $a, array $b): int => $b['hours'] <=> $a['hours']);

        $totalHours = array_sum(array_column($byProject, 'hours'));
        $top = array_slice($byProject, 0, self::LIMIT);

        $projects = [];
        foreach ($top as $row) {
            $projects[] = [
                'project' => $row['project'],
                'duration' => CarbonInterval::hours($row['hours']),
                'percent' => $totalHours > 0 ? ($row['hours'] / $totalHours) * 100 : 0.0,
            ];
        }

        return [
            'projects' => $projects,
            'total' => CarbonInterval::hours($totalHours),
        ];
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (! $user instanceof User) {
            throw new LogicException('TopProjects requires an authenticated User.');
        }

        return $user;
    }
}
