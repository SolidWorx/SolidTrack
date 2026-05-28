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

namespace App\Repository;

use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use App\Report\ReportFilter;
use App\Stats\UsageSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use SolidWorx\Platform\PlatformBundle\Repository\EntityRepository;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

/**
 * @extends EntityRepository<TimeEntry>
 */
final class TimeEntryRepository extends EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    public function findActiveTrackersForUser(User $user): ?TimeEntry
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.entryType = :entryType')
            ->andWhere('t.user = :user')
            ->setParameter('status', TimeEntryStatus::TRACKING)
            ->setParameter('entryType', TimeEntryType::TRACKING)
            ->setParameter('user', $user->getId(), UlidType::NAME)
            ->orderBy('t.dateStart', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return iterable<TimeEntry>
     */
    public function findAllCompleteTrackers(): iterable
    {
        $lastWeek = CarbonPeriod::create('now', CarbonInterval::days(-1), 7);

        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.dateStart BETWEEN :start AND :end')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('start', $lastWeek->getIncludedEndDate()->startOfDay())
            ->setParameter('end', $lastWeek->getStartDate()->endOfDay())
            ->orderBy('t.dateEnd', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return iterable<TimeEntry>
     */
    public function findCompletedTrackersForUserInRange(User $user, DateTimeInterface $start, DateTimeInterface $end): iterable
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->where('t.status = :status')
            ->andWhere('t.user = :user')
            ->andWhere('t.dateStart BETWEEN :start AND :end')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('user', $user->getId(), UlidType::NAME)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, UsageSummary> Keyed by project id (RFC 4122).
     */
    public function aggregateByProjectForUser(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array
    {
        /** @var array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc */
        $acc = [];

        foreach ($this->findCompletedForUserInPeriod($user, $from, $to) as $entry) {
            $project = $entry->getProject();
            $duration = $entry->getDuration();
            $key = $project?->getId()?->toRfc4122();
            if ($project === null || $duration === null || $key === null) {
                continue;
            }

            $this->fold(
                $acc,
                $key,
                $entry,
                $duration->totalHours,
                $project->getHourlyRate(),
                $project->getClient()?->getCurrency(),
            );
        }

        return $this->materialise($acc);
    }

    /**
     * @return array<string, UsageSummary> Keyed by client id (RFC 4122).
     */
    public function aggregateByClientForUser(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array
    {
        /** @var array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc */
        $acc = [];

        foreach ($this->findCompletedForUserInPeriod($user, $from, $to) as $entry) {
            $project = $entry->getProject();
            $duration = $entry->getDuration();
            $client = $project?->getClient();
            $key = $client?->getId()?->toRfc4122();
            if ($duration === null || $client === null || $key === null) {
                continue;
            }

            $this->fold(
                $acc,
                $key,
                $entry,
                $duration->totalHours,
                $project->getHourlyRate(),
                $client->getCurrency(),
            );
        }

        return $this->materialise($acc);
    }

    /**
     * @param array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc
     */
    private function fold(array &$acc, string $key, TimeEntry $entry, float $hours, ?float $rate, ?string $currency): void
    {
        $acc[$key] ??= ['total' => 0.0, 'billable' => 0.0, 'amount' => 0.0, 'currency' => $currency, 'last' => null];
        $acc[$key]['total'] += $hours;

        if ($entry->isBillable()) {
            $acc[$key]['billable'] += $hours;
            if ($rate !== null) {
                $acc[$key]['amount'] += $hours * $rate;
            }
        }

        $end = $entry->getDateEnd();
        if ($end !== null && ($acc[$key]['last'] === null || $end->greaterThan($acc[$key]['last']))) {
            $acc[$key]['last'] = $end;
        }
    }

    /**
     * @param array<string, array{total: float, billable: float, amount: float, currency: ?string, last: ?CarbonImmutable}> $acc
     *
     * @return array<string, UsageSummary>
     */
    private function materialise(array $acc): array
    {
        return array_map(
            static fn (array $row): UsageSummary => new UsageSummary(
                CarbonInterval::hours($row['total']),
                CarbonInterval::hours($row['billable']),
                $row['amount'],
                $row['currency'],
                $row['last'],
            ),
            $acc,
        );
    }

    /**
     * @return list<TimeEntry>
     */
    private function findCompletedForUserInPeriod(User $user, ?DateTimeInterface $from, ?DateTimeInterface $to): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->leftJoin('p.client', 'c')
            ->addSelect('c')
            ->where('t.status = :status')
            ->andWhere('t.user = :user')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('user', $user->getId(), UlidType::NAME);

        if ($from !== null) {
            $qb->andWhere('t.dateStart >= :from')
                ->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb->andWhere('t.dateStart <= :to')
                ->setParameter('to', $to);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return iterable<TimeEntry>
     */
    public function findCompleteTrackersForUser(User $user, int $limit = 8): iterable
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.user = :user')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('user', $user->getId(), UlidType::NAME)
            ->orderBy('t.dateEnd', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<TimeEntry>
     */
    public function findForReport(User $user, ReportFilter $filter, ?int $page = null, int $perPage = 50): array
    {
        $qb = $this->reportQueryBuilder($user, $filter)
            ->addSelect('p')
            ->leftJoin('t.tags', 't_tags')
            ->addSelect('t_tags')
            ->orderBy('t.dateStart', 'DESC');

        if ($page !== null) {
            $qb->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);
        }

        return $qb->getQuery()->getResult();
    }

    public function countForReport(User $user, ReportFilter $filter): int
    {
        return (int) $this->reportQueryBuilder($user, $filter)
            ->select('COUNT(DISTINCT t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function reportQueryBuilder(User $user, ReportFilter $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->where('t.status = :status')
            ->andWhere('t.user = :user')
            ->andWhere('t.dateStart BETWEEN :from AND :to')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('user', $user->getId(), UlidType::NAME)
            ->setParameter('from', $filter->from)
            ->setParameter('to', $filter->to);

        if ($filter->projectId !== null) {
            $qb->andWhere('t.project = :projectId')
                ->setParameter('projectId', $filter->projectId, UlidType::NAME);
        }

        if ($filter->clientId !== null) {
            $qb->andWhere('p.client = :clientId')
                ->setParameter('clientId', $filter->clientId, UlidType::NAME);
        }

        if ($filter->tagIds !== []) {
            $sub = $this->getEntityManager()->createQueryBuilder()
                ->select('t2.id')
                ->from(TimeEntry::class, 't2')
                ->join('t2.tags', 'tg2')
                ->where('tg2.id IN (:tagIds)')
                ->getDQL();
            $qb->andWhere($qb->expr()->in('t.id', $sub))
                ->setParameter(
                    'tagIds',
                    array_map(static fn (Ulid $id): string => $id->toBinary(), $filter->tagIds),
                    ArrayParameterType::STRING,
                );
        }

        if ($filter->billable !== null) {
            $qb->andWhere('t.billable = :billable')
                ->setParameter('billable', $filter->billable);
        }

        return $qb;
    }
}
