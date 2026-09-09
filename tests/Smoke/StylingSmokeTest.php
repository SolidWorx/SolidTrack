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

namespace App\Test\Smoke;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\Tag;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Enum\TimeEntryStatus;
use App\Enum\TimeEntryType;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Every page renders.
 *
 * The templates share a handful of building blocks (`StatCard`, `_tag_chips`,
 * `_pagination`, the platform's page-header blocks), so a change to one of them
 * can break pages far from where it was edited.
 */
#[CoversNothing]
final class StylingSmokeTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function pages(): iterable
    {
        yield 'dashboard' => ['/'];
        yield 'report summary' => ['/reports?type=summary'];
        yield 'report detailed' => ['/reports?type=detailed'];
        yield 'projects' => ['/projects'];
        yield 'clients' => ['/clients'];
        yield 'tags' => ['/tags'];
        yield 'new project' => ['/projects/new'];
        yield 'new client' => ['/clients/new'];
        yield 'new tag' => ['/tags/new'];
    }

    #[DataProvider('pages')]
    public function testPageRenders(string $uri): void
    {
        $browser = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $user = new User();
        $user->setEmail('smoke@example.test')
            ->setEnabled(true)
            ->setVerified(true)
            ->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $em->persist($user);

        $clientEntity = new Client();
        $clientEntity->setName('Acme')
            ->setCurrency('USD');
        $em->persist($clientEntity);

        $project = new Project();
        $project->setName('Marketing Website');
        $project->setClient($clientEntity)
            ->setHourlyRate(100.0);
        $em->persist($project);

        $tag = new Tag();
        $tag->setName('design')
            ->setColor('#e91e63');
        $em->persist($tag);

        $entry = new TimeEntry();
        $entry->setUser($user)
            ->setProject($project)
            ->setDateStart(CarbonImmutable::now()->subHours(3))
            ->setDateEnd(CarbonImmutable::now()->subHour())
            ->setBillable(true)
            ->setDescription('Landing page work')
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::MANUAL);
        $entry->addTag($tag);
        $em->persist($entry);

        $entry2 = new TimeEntry();
        $entry2->setUser($user)
            ->setProject($project)
            ->setDateStart(CarbonImmutable::now()->subHours(8))
            ->setDateEnd(CarbonImmutable::now()->subHours(7))
            ->setBillable(false)
            ->setDescription('Standup')
            ->setStatus(TimeEntryStatus::COMPLETED)
            ->setEntryType(TimeEntryType::MANUAL);
        $em->persist($entry2);

        $em->flush();

        $browser->loginUser($user);
        // `/projects` and `/clients` 301 to a trailing-slash duplicate route.
        $browser->followRedirects();
        $browser->request('GET', $uri);

        self::assertResponseIsSuccessful();
    }
}
