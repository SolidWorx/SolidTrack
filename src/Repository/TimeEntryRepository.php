<?php

namespace App\Repository;

use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<TimeEntry>
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    public function findActiveTrackersForUser(User $user): iterable
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.entryType = :entryType')
            ->andWhere('t.user = :user')
            ->setParameter('status', TimeEntryStatus::TRACKING)
            ->setParameter('entryType', TimeEntryType::TRACKING)
            ->setParameter('user', $user->getId(), UlidType::NAME)
            ->orderBy('t.dateStart', 'DESC')
            ->getQuery()
            ->getResult();
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
    public function findCompleteTrackersForUser(User $user): iterable
    {
        $lastWeek = CarbonPeriod::create('now', CarbonInterval::days(-1), 7);

        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.dateStart BETWEEN :start AND :end')
            ->andWhere('t.user = :user')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('start', $lastWeek->getIncludedEndDate()->startOfDay())
            ->setParameter('end', $lastWeek->getStartDate()->endOfDay())
            ->setParameter('user', $user->getId(), UlidType::NAME)
            ->orderBy('t.dateEnd', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function remove(TimeEntry $entry): void
    {
        $this->getEntityManager()->remove($entry);
        $this->getEntityManager()->flush();
    }
}
