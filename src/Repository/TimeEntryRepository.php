<?php

namespace App\Repository;

use App\Entity\TimeEntry;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeEntry>
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    public function findActiveTrackers(): iterable
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.entryType = :entryType')
            ->setParameter('status', TimeEntryStatus::TRACKING)
            ->setParameter('entryType', TimeEntryType::TRACKING)
            ->orderBy('t.dateStart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return iterable<TimeEntry>
     */
    public function findCompleteTrackers(): iterable
    {
        $lastWeek = CarbonPeriod::create('now', CarbonInterval::days(-1), 7);

        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.dateStart BETWEEN :start AND :end')
            ->setParameter('status', TimeEntryStatus::COMPLETED)
            ->setParameter('start', $lastWeek->getIncludedEndDate()->startOfDay())
            ->setParameter('end', $lastWeek->getStartDate()->endOfDay())
            ->orderBy('t.dateStart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCompleteTrackersGroupedByDate(): iterable
    {
        $currentDate = null;

        foreach ($this->findCompleteTrackers() as $entry) {
            $date = $entry->getDateStart()->format('Y-m-d');

            if ($date !== $currentDate) {
                $currentDate = $date;
                yield $date => [];
            }

            yield $date => $entry;
        }
    }
}
