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

namespace App\Test\Twig\Components;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\Tag;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use App\Repository\TimeEntryRepository;
use App\Twig\Components\UserActivity;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Covers the one-click "resume this entry" action on the recent-activity list.
 *
 * Driven through the component object rather than over HTTP: the LiveComponent test
 * client goes through the kernel, and the platform's tenant scope guard currently
 * redirects every authenticated request to an onboarding route SolidTrack does not
 * register (see the "Tenant-scope the domain entities" backlog task).
 */
#[CoversClass(UserActivity::class)]
final class UserActivityResumeTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    private TimeEntryRepository $repository;

    private UserActivity $component;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $manager = $container->get('doctrine')->getManager();
        \assert($manager instanceof EntityManagerInterface);
        $this->em = $manager;
        $this->repository = $this->em->getRepository(TimeEntry::class);

        // addFlash() needs a session, and the component resolves the current user
        // from the token storage.
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack = $container->get(RequestStack::class);
        \assert($requestStack instanceof RequestStack);
        $requestStack->push($request);

        $component = $container->get(UserActivity::class);
        \assert($component instanceof UserActivity);
        $this->component = $component;
    }

    public function testResumeStartsANewTrackerCopyingTheOriginalEntry(): void
    {
        $user = $this->createUser('owner@example.test');
        $project = $this->createProject('Website');
        $tag = $this->createTag('resume-test-' . bin2hex(random_bytes(4)));

        $original = $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00');
        $original->setDescription('Weekly sync')->setBillable(false)->addTag($tag);
        $this->em->flush();

        $this->authenticate($user);
        $this->component->resumeEntry($original);
        $this->em->flush();

        $running = $this->repository->findActiveTrackersForUser($user);

        self::assertNotNull($running);
        self::assertNotSame($original->getId()?->toRfc4122(), $running->getId()?->toRfc4122());
        self::assertSame('Weekly sync', $running->getDescription());
        self::assertSame($project->getId()?->toRfc4122(), $running->getProject()?->getId()?->toRfc4122());
        self::assertFalse($running->isBillable());
        self::assertSame(TimeEntryStatus::TRACKING, $running->getStatus());
        self::assertSame(TimeEntryType::TRACKING, $running->getEntryType());
        self::assertNull($running->getDateEnd());
        self::assertCount(1, $running->getTags());
        self::assertSame($tag->getName(), $running->getTags()->first()->getName());

        // The entry that was resumed is untouched.
        self::assertSame(TimeEntryStatus::COMPLETED, $original->getStatus());
    }

    public function testResumeStopsATimerThatIsAlreadyRunning(): void
    {
        $user = $this->createUser('owner@example.test');
        $project = $this->createProject('Website');

        $original = $this->createEntry($user, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00');
        $original->setDescription('Weekly sync');

        $alreadyRunning = new TimeEntry();
        $alreadyRunning->setUser($user)
            ->setProject($project)
            ->setDescription('Something else')
            ->setDateStart(CarbonImmutable::now()->subHour())
            ->setStatus(TimeEntryStatus::TRACKING)
            ->setEntryType(TimeEntryType::TRACKING);
        $this->em->persist($alreadyRunning);
        $this->em->flush();

        $this->authenticate($user);
        $this->component->resumeEntry($original);
        $this->em->flush();

        self::assertSame(TimeEntryStatus::COMPLETED, $alreadyRunning->getStatus());
        self::assertNotNull($alreadyRunning->getDateEnd());

        $running = $this->repository->findActiveTrackersForUser($user);
        self::assertNotNull($running);
        self::assertSame('Weekly sync', $running->getDescription());
    }

    public function testResumeRefusesAnEntryBelongingToAnotherUser(): void
    {
        $owner = $this->createUser('owner@example.test');
        $intruder = $this->createUser('intruder@example.test');
        $project = $this->createProject('Website');

        $entry = $this->createEntry($owner, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00');
        $this->em->flush();

        $this->authenticate($intruder);

        $this->expectException(AccessDeniedException::class);
        $this->component->resumeEntry($entry);
    }

    public function testRemovingAnEntryBelongingToAnotherUserIsRefused(): void
    {
        $owner = $this->createUser('owner@example.test');
        $intruder = $this->createUser('intruder@example.test');
        $project = $this->createProject('Website');

        $entry = $this->createEntry($owner, $project, '2026-05-10 09:00:00', '2026-05-10 11:00:00');
        $this->em->flush();

        $this->authenticate($intruder);

        $this->expectException(AccessDeniedException::class);
        $this->component->removeItem($entry);
    }

    private function authenticate(User $user): void
    {
        $tokenStorage = self::getContainer()->get(TokenStorageInterface::class);
        \assert($tokenStorage instanceof TokenStorageInterface);
        $tokenStorage->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));
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

    private function createProject(string $name): Project
    {
        $client = new Client();
        $client->setName('Acme')->setCurrency('USD');
        $this->em->persist($client);

        $project = new Project();
        $project->setName($name);
        $project->setClient($client);
        $this->em->persist($project);

        return $project;
    }

    private function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->setName($name);
        $this->em->persist($tag);

        return $tag;
    }

    private function createEntry(User $user, Project $project, string $start, string $end): TimeEntry
    {
        $entry = new TimeEntry();
        $entry->setUser($user)
            ->setProject($project)
            ->setDateStart(CarbonImmutable::parse($start))
            ->setDateEnd(CarbonImmutable::parse($end))
            ->setBillable(true)
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::TRACKING);
        $this->em->persist($entry);

        return $entry;
    }
}
