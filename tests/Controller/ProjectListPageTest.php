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

namespace App\Test\Controller;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(\App\Twig\Components\ProjectList::class)]
final class ProjectListPageTest extends WebTestCase
{
    public function testListShowsProjectWithTrackedTime(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $user = new User();
        $user->setEmail('owner@example.test')->setEnabled(true)->setVerified(true)->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $em->persist($user);

        $clientEntity = new Client();
        $clientEntity->setName('Acme')->setCurrency('USD');
        $em->persist($clientEntity);

        $project = new Project();
        $project->setName('Marketing Website');
        $project->setClient($clientEntity);
        $project->setHourlyRate(100.0);
        $em->persist($project);

        $entry = new TimeEntry();
        $entry->setUser($user)
            ->setProject($project)
            ->setDateStart(CarbonImmutable::parse('2026-05-10 09:00:00'))
            ->setDateEnd(CarbonImmutable::parse('2026-05-10 11:00:00'))
            ->setBillable(true)
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::MANUAL);
        $em->persist($entry);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/projects');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Marketing Website');
        self::assertSelectorTextContains('body', 'All time');
    }
}
