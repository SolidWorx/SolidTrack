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
use App\Entity\User;
use App\Twig\Components\ManualTimeEntry;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The manual-entry modal is shared by every page that offers to capture time, so
 * what matters per page is that a trigger is present and that exactly one modal
 * is rendered for all the triggers on it to point at.
 */
#[CoversClass(ManualTimeEntry::class)]
final class ManualEntryModalPageTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function pageProvider(): iterable
    {
        yield 'dashboard' => ['/dashboard'];
        yield 'projects list' => ['/projects/'];
        yield 'clients list' => ['/clients/'];
        yield 'detailed report' => ['/reports?type=detailed'];
        yield 'summary report' => ['/reports?type=summary'];
    }

    #[DataProvider('pageProvider')]
    public function testThePageOffersTheManualEntryModal(string $path): void
    {
        $browser = $this->browserWithFixtures();
        $browser->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[data-bs-target="#manual-time-entry"]');
        self::assertSelectorExists('#manual-time-entry');
        self::assertSelectorTextContains('#manual-time-entry', 'Starts at');
        self::assertSelectorTextContains('#manual-time-entry', 'Ends at');
    }

    public function testTheProjectPageTriggerPreSelectsThatProject(): void
    {
        $browser = $this->browserWithFixtures();
        $project = $this->findProject();

        $browser->request('GET', '/projects/' . $project->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorExists(
            sprintf('button[data-bs-target="#manual-time-entry"][data-prefill-project="%s"]', $project->getId())
        );
    }

    public function testTheProjectsListOffersOneTriggerPerRowAndASingleModal(): void
    {
        $browser = $this->browserWithFixtures();
        $crawler = $browser->request('GET', '/projects/');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('#manual-time-entry'));
        self::assertGreaterThanOrEqual(
            1,
            $crawler->filter('button[data-bs-target="#manual-time-entry"][data-prefill-project]')
                ->count()
        );
    }

    public function testTheClientsListTriggerNarrowsToThatClient(): void
    {
        $browser = $this->browserWithFixtures();
        $crawler = $browser->request('GET', '/clients/');

        self::assertResponseIsSuccessful();

        $client = $this->findClient();
        self::assertSelectorExists(
            sprintf('button[data-bs-target="#manual-time-entry"][data-prefill-client="%s"]', $client->getId())
        );

        // The narrowing is done in the browser, so the modal has to carry the
        // project => client map for the controller to work from.
        $map = $crawler->filter('[data-project-clients]')
            ->attr('data-project-clients');
        self::assertIsString($map);

        $decoded = json_decode($map, true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertArrayHasKey((string) $this->findProject()->getId(), $decoded);
        self::assertSame((string) $client->getId(), $decoded[(string) $this->findProject()->getId()]);
    }

    private function browserWithFixtures(): KernelBrowser
    {
        $browser = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $user = new User();
        $user->setEmail('owner@example.test')
            ->setEnabled(true)
            ->setVerified(true)
            ->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $em->persist($user);

        $client = new Client();
        $client->setName('Acme')
            ->setCurrency('USD');
        $em->persist($client);

        $project = new Project();
        $project->setName('Marketing Website');
        $project->setClient($client);
        $em->persist($project);

        $em->flush();

        $browser->loginUser($user);

        return $browser;
    }

    private function findProject(): Project
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $project = $em->getRepository(Project::class)->findOneBy(['name' => 'Marketing Website']);
        \assert($project instanceof Project);

        return $project;
    }

    private function findClient(): Client
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $client = $em->getRepository(Client::class)->findOneBy(['name' => 'Acme']);
        \assert($client instanceof Client);

        return $client;
    }
}
