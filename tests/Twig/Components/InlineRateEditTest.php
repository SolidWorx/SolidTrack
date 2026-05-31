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
use App\Entity\User;
use App\Twig\Components\InlineRateEdit;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(InlineRateEdit::class)]
final class InlineRateEditTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private EntityManagerInterface $em;

    private User $user;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        \assert($this->em instanceof EntityManagerInterface);

        $this->user = new User();
        $this->user->setEmail('rate-test@example.test')
            ->setEnabled(true)
            ->setVerified(true)
            ->setRoles(['ROLE_USER'])
            ->setPassword('hashed');
        $this->em->persist($this->user);
        $this->em->flush();
    }

    private function makeProject(?float $rate = null, string $currency = 'USD'): Project
    {
        $client = new Client();
        $client->setName('Acme')->setCurrency($currency);
        $this->em->persist($client);

        $project = new Project();
        $project->setName('Test Project');
        $project->setClient($client);
        if ($rate !== null) {
            $project->setHourlyRate($rate);
        }
        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function testRendersFormattedRateInReadMode(): void
    {
        $project = $this->makeProject(75.0, 'USD');

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component->render()->toString();

        self::assertStringContainsString('$75.00', $html);
        self::assertStringContainsString('/ hour', $html);
        self::assertStringNotContainsString('<input', $html);
    }

    public function testRendersNoRateMessageWhenRateIsNull(): void
    {
        $project = $this->makeProject(null);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component->render()->toString();

        self::assertStringContainsString('No rate set', $html);
        self::assertStringNotContainsString('<input', $html);
    }

    public function testStartEditSwitchesToEditMode(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component->call('startEdit')->render()->toString();

        self::assertStringContainsString('<input', $html);
        self::assertStringContainsString('value="50"', $html);
    }

    public function testSaveValidRatePersistsAndReturnsToReadMode(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '120')
            ->call('save')
            ->render()
            ->toString();

        self::assertStringContainsString('$120.00', $html);
        self::assertStringNotContainsString('<input', $html);

        $this->em->clear();
        $refreshed = $this->em->find(Project::class, $project->getId());
        self::assertNotNull($refreshed);
        self::assertSame(120.0, $refreshed->getHourlyRate());
    }

    public function testSaveZeroRateIsValidAndPersists(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '0')
            ->call('save')
            ->render()
            ->toString();

        self::assertStringContainsString('$0.00', $html);
        self::assertStringNotContainsString('<input', $html);

        $this->em->clear();
        $refreshed = $this->em->find(Project::class, $project->getId());
        self::assertSame(0.0, $refreshed?->getHourlyRate());
    }

    public function testSaveEmptyRateClearsToNull(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '')
            ->call('save')
            ->render()
            ->toString();

        self::assertStringContainsString('No rate set', $html);
        self::assertStringNotContainsString('<input', $html);

        $this->em->clear();
        $refreshed = $this->em->find(Project::class, $project->getId());
        self::assertNotNull($refreshed);
        self::assertNull($refreshed->getHourlyRate());
    }

    public function testSaveInvalidRateShowsErrorAndDoesNotPersist(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '-10')
            ->call('save')
            ->render()
            ->toString();

        self::assertStringContainsString('<input', $html);
        self::assertStringContainsString('Rate must be 0 or greater', $html);

        $this->em->clear();
        $refreshed = $this->em->find(Project::class, $project->getId());
        self::assertNotNull($refreshed);
        self::assertSame(50.0, $refreshed->getHourlyRate());
    }

    public function testCancelRevertsRateAndReturnsToReadMode(): void
    {
        $project = $this->makeProject(50.0);

        $component = $this->createLiveComponent('InlineRateEdit', ['project' => $project])
            ->actingAs($this->user);

        $html = $component
            ->call('startEdit')
            ->set('rate', '999')
            ->call('cancel')
            ->render()
            ->toString();

        self::assertStringContainsString('$50.00', $html);
        self::assertStringNotContainsString('<input', $html);

        $this->em->clear();
        $refreshed = $this->em->find(Project::class, $project->getId());
        self::assertNotNull($refreshed);
        self::assertSame(50.0, $refreshed->getHourlyRate());
    }
}
