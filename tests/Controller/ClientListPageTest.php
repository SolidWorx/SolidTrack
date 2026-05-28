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
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(\App\Twig\Components\ClientList::class)]
final class ClientListPageTest extends WebTestCase
{
    public function testListShowsClient(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);

        $user = new User();
        $user->setEmail('owner@example.test')->setEnabled(true)->setVerified(true)->setRoles(['ROLE_USER']);
        $user->setPassword('hashed');
        $em->persist($user);

        $clientEntity = new Client();
        $clientEntity->setName('Globex Corp')->setCurrency('USD');
        $em->persist($clientEntity);
        $em->flush();

        $client->loginUser($user);
        $client->request('GET', '/clients/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Globex Corp');
        self::assertSelectorTextContains('body', 'Last activity');
    }
}
