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
use App\Form\AbstractTimeEntryType;
use App\Repository\TimeEntryRepository;
use App\Twig\Components\ManualTimeEntry;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Covers capturing a time entry by hand, for work that was not timed live.
 *
 * Driven through the component object so the save action and its validation can be
 * exercised directly; ManualEntryModalPageTest covers the rendered side over HTTP.
 */
#[CoversClass(ManualTimeEntry::class)]
final class ManualTimeEntryTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    private TimeEntryRepository $repository;

    private ManualTimeEntry $component;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $manager = $container->get('doctrine')
            ->getManager();
        \assert($manager instanceof EntityManagerInterface);
        $this->em = $manager;
        $this->repository = $this->em->getRepository(TimeEntry::class);

        // addFlash() and the session-backed CSRF token manager both need a session.
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack = $container->get(RequestStack::class);
        \assert($requestStack instanceof RequestStack);
        $requestStack->push($request);

        $component = $container->get(ManualTimeEntry::class);
        \assert($component instanceof ManualTimeEntry);
        $this->component = $component;
    }

    public function testSaveCapturesACompletedManualEntryForTheCurrentUser(): void
    {
        $user = $this->createUser('owner@example.test');
        $project = $this->createProject('Website');
        $tag = $this->createTag('manual-test-' . bin2hex(random_bytes(4)));
        $this->em->flush();

        $this->authenticate($user);
        $this->component->returnUrl = '/dashboard';
        $this->component->formValues = $this->formValues([
            'description' => 'Refactoring the invoice parser',
            'project' => (string) $project->getId(),
            'tags' => [(string) $tag->getId()],
            'billable' => '1',
            'dateStart' => '2026-09-01T09:00',
            'dateEnd' => '2026-09-01T11:30',
        ]);

        $response = $this->component->save();
        $this->em->flush();

        self::assertSame('/dashboard', $response->getTargetUrl());

        $entries = $this->repository->findBy(['user' => $user]);
        self::assertCount(1, $entries);

        $entry = $entries[0];
        self::assertSame('Refactoring the invoice parser', $entry->getDescription());
        self::assertSame($project->getId()?->toRfc4122(), $entry->getProject()?->getId()?->toRfc4122());
        self::assertTrue($entry->isBillable());
        self::assertSame(TimeEntryStatus::COMPLETED, $entry->getStatus());
        self::assertSame(TimeEntryType::MANUAL, $entry->getEntryType());
        self::assertSame('2026-09-01 09:00', $entry->getDateStart()?->format('Y-m-d H:i'));
        self::assertSame('2026-09-01 11:30', $entry->getDateEnd()?->format('Y-m-d H:i'));
        self::assertSame(150.0, $entry->getDuration()?->totalMinutes);
        self::assertCount(1, $entry->getTags());
        $firstTag = $entry->getTags()
            ->first();
        self::assertInstanceOf(Tag::class, $firstTag);
        self::assertSame($tag->getName(), $firstTag->getName());
    }

    public function testSaveCapturesAnEntryWithoutAProjectOrDescription(): void
    {
        $user = $this->createUser('owner@example.test');
        $this->em->flush();

        $this->authenticate($user);
        $this->component->formValues = $this->formValues([
            'dateStart' => '2026-09-01T09:00',
            'dateEnd' => '2026-09-01T09:45',
        ]);

        $this->component->save();
        $this->em->flush();

        $entries = $this->repository->findBy(['user' => $user]);
        self::assertCount(1, $entries);
        self::assertNull($entries[0]->getProject());
        self::assertFalse($entries[0]->isBillable());
    }

    public function testSaveRejectsAnEndBeforeTheStart(): void
    {
        $user = $this->createUser('owner@example.test');
        $this->em->flush();

        $this->authenticate($user);
        $this->component->formValues = $this->formValues([
            'dateStart' => '2026-09-01T11:30',
            'dateEnd' => '2026-09-01T09:00',
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessageMatches('/end time must be after the start time/');

        $this->component->save();
    }

    public function testSaveRejectsAnEndEqualToTheStart(): void
    {
        $user = $this->createUser('owner@example.test');
        $this->em->flush();

        $this->authenticate($user);
        $this->component->formValues = $this->formValues([
            'dateStart' => '2026-09-01T09:00',
            'dateEnd' => '2026-09-01T09:00',
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->component->save();
    }

    public function testSaveRequiresBothEndsOfThePeriod(): void
    {
        $user = $this->createUser('owner@example.test');
        $this->em->flush();

        $this->authenticate($user);
        $this->component->formValues = $this->formValues([
            'dateStart' => '2026-09-01T09:00',
            'dateEnd' => '',
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessageMatches('/when the work ended/');

        $this->component->save();
    }

    public function testSaveRejectsAForgedCsrfToken(): void
    {
        $user = $this->createUser('owner@example.test');
        $this->em->flush();

        $this->authenticate($user);
        $values = $this->formValues([
            'dateStart' => '2026-09-01T09:00',
            'dateEnd' => '2026-09-01T11:30',
        ]);
        $values['_token'] = 'forged';
        $this->component->formValues = $values;

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessageMatches('/CSRF token is invalid/');

        $this->component->save();
    }

    public function testSaveWithoutAnAuthenticatedUserIsRefused(): void
    {
        $this->component->formValues = $this->formValues([
            'dateStart' => '2026-09-01T09:00',
            'dateEnd' => '2026-09-01T11:30',
        ]);

        $this->expectException(LogicException::class);

        $this->component->save();
    }

    /**
     * Mirrors what the browser posts back: every field the form renders, plus a
     * real CSRF token for the entry forms' own (session-backed) token id.
     *
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function formValues(array $overrides = []): array
    {
        $csrf = self::getContainer()->get(CsrfTokenManagerInterface::class);
        \assert($csrf instanceof CsrfTokenManagerInterface);

        return array_merge([
            'description' => '',
            'project' => '',
            'tags' => [],
            'billable' => null,
            'dateStart' => '',
            'dateEnd' => '',
            '_token' => $csrf->getToken(AbstractTimeEntryType::CSRF_TOKEN_ID)->getValue(),
        ], $overrides);
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
        $client->setName('Acme')
            ->setCurrency('USD');
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
}
