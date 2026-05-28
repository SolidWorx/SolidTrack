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

namespace App\Test\Repository;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use App\Repository\TimeEntryRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(TimeEntryRepository::class)]
final class TimeEntryAggregateTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    private TimeEntryRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->em->getRepository(TimeEntry::class);
    }

    public function testAggregateByProjectSumsTrackedTimeAndBillableEarnings(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Website', $client, 100.0);

        // 2h billable -> $200, 1h non-billable -> $0.
        $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true);
        $this->createEntry($user, $project, '2026-05-11 09:00:00', '2026-05-11 10:00:00', false);
        $this->em->flush();

        $summaries = $this->repository->aggregateByProjectForUser($user, null, null);
        $key = $project->getId()->toRfc4122();

        self::assertArrayHasKey($key, $summaries);
        self::assertEqualsWithDelta(3.0, $summaries[$key]->totalDuration->totalHours, 0.001);
        self::assertEqualsWithDelta(2.0, $summaries[$key]->billableDuration->totalHours, 0.001);
        self::assertEqualsWithDelta(200.0, $summaries[$key]->amount, 0.001);
        self::assertSame('USD', $summaries[$key]->currency);
    }

    public function testProjectWithoutRateEarnsNothing(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Internal', $client, null);

        $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true);
        $this->em->flush();

        $summaries = $this->repository->aggregateByProjectForUser($user, null, null);
        $key = $project->getId()->toRfc4122();

        self::assertEqualsWithDelta(0.0, $summaries[$key]->amount, 0.001);
        self::assertEqualsWithDelta(2.0, $summaries[$key]->totalDuration->totalHours, 0.001);
    }

    public function testRangeFilterExcludesEntriesOutsideWindow(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Website', $client, 100.0);

        $this->createEntry($user, $project, '2026-04-30 09:00:00', '2026-04-30 11:00:00', true); // April
        $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 12:00:00', true); // May
        $this->em->flush();

        $from = CarbonImmutable::parse('2026-05-01 00:00:00');
        $to = CarbonImmutable::parse('2026-05-31 23:59:59');

        $summaries = $this->repository->aggregateByProjectForUser($user, $from, $to);
        $key = $project->getId()->toRfc4122();

        self::assertCount(1, $summaries);
        self::assertEqualsWithDelta(3.0, $summaries[$key]->totalDuration->totalHours, 0.001);
        self::assertSame(
            '2026-05-10',
            $summaries[$key]->lastActivity->format('Y-m-d'),
        );
    }

    public function testAggregateIsScopedToTheGivenUser(): void
    {
        $owner = $this->createUser('owner@example.test');
        $other = $this->createUser('other@example.test');
        $client = $this->createClient('Acme', 'USD');
        $project = $this->createProject('Website', $client, 100.0);

        $this->createEntry($owner, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true);
        $this->createEntry($other, $project, '2026-05-10 09:00:00', '2026-05-10 15:00:00', true);
        $this->em->flush();

        $summaries = $this->repository->aggregateByProjectForUser($owner, null, null);
        $key = $project->getId()->toRfc4122();

        self::assertEqualsWithDelta(2.0, $summaries[$key]->totalDuration->totalHours, 0.001);
    }

    public function testAggregateByClientGroupsAcrossProjects(): void
    {
        $user = $this->createUser('owner@example.test');
        $client = $this->createClient('Acme', 'EUR');
        $projectA = $this->createProject('Site', $client, 100.0);
        $projectB = $this->createProject('App', $client, 50.0);

        $this->createEntry($user, $projectA, '2026-05-10 09:00:00', '2026-05-10 11:00:00', true); // 2h * 100 = 200
        $this->createEntry($user, $projectB, '2026-05-11 09:00:00', '2026-05-11 13:00:00', true); // 4h * 50 = 200
        $this->em->flush();

        $summaries = $this->repository->aggregateByClientForUser($user, null, null);
        $key = $client->getId()->toRfc4122();

        self::assertEqualsWithDelta(6.0, $summaries[$key]->totalDuration->totalHours, 0.001);
        self::assertEqualsWithDelta(400.0, $summaries[$key]->amount, 0.001);
        self::assertSame('EUR', $summaries[$key]->currency);
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email)
            ->setEnabled(true)
            ->setVerified(true)
            ->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $this->em->persist($user);

        return $user;
    }

    private function createClient(string $name, string $currency): Client
    {
        $client = new Client();
        $client->setName($name)->setCurrency($currency);
        $this->em->persist($client);

        return $client;
    }

    private function createProject(string $name, Client $client, ?float $rate): Project
    {
        $project = new Project();
        $project->setName($name);
        $project->setClient($client);
        $project->setHourlyRate($rate);
        $this->em->persist($project);

        return $project;
    }

    private function createEntry(
        User $user,
        Project $project,
        string $start,
        string $end,
        bool $billable,
    ): TimeEntry {
        $entry = new TimeEntry();
        $entry->setUser($user)
            ->setProject($project)
            ->setDateStart(CarbonImmutable::parse($start))
            ->setDateEnd(CarbonImmutable::parse($end))
            ->setBillable($billable)
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::MANUAL);
        $this->em->persist($entry);

        return $entry;
    }
}
