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
