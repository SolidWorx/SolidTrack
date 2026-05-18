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
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use SolidWorx\Platform\PlatformBundle\Repository\EntityRepository;
use Symfony\Bridge\Doctrine\Types\UlidType;

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
}
