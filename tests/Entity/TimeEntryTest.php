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

namespace App\Test\Entity;

use App\Entity\Project;
use App\Entity\TimeEntry;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeEntry::class)]
final class TimeEntryTest extends TestCase
{
    public function testStringCastUsesTheDescriptionWhenSet(): void
    {
        $entry = (new TimeEntry())->setDescription('Rebuilding the invoice importer');

        self::assertSame('Rebuilding the invoice importer', (string) $entry);
    }

    public function testStringCastFallsBackToTheProjectWhenTheDescriptionIsNull(): void
    {
        $project = new Project();
        $project->setName('Acme redesign');

        $entry = (new TimeEntry())
            ->setProject($project)
            ->setDateStart(CarbonImmutable::parse('2026-08-30 09:15:00'));

        self::assertSame('Acme redesign', (string) $entry);
    }

    public function testStringCastFallsBackToTheStartTimeWithoutADescriptionOrProject(): void
    {
        $entry = (new TimeEntry())->setDateStart(CarbonImmutable::parse('2026-08-30 09:15:00'));

        self::assertSame('2026-08-30 09:15', (string) $entry);
    }

    public function testStringCastOfAnEmptyEntryDoesNotThrow(): void
    {
        self::assertSame('', (string) new TimeEntry());
    }

    public function testStringCastTreatsAnEmptyDescriptionAsMissing(): void
    {
        $project = new Project();
        $project->setName('Acme redesign');

        $entry = (new TimeEntry())
            ->setDescription('')
            ->setProject($project);

        self::assertSame('Acme redesign', (string) $entry);
    }

    public function testDateSettersNormalisePlainDateTimesToCarbon(): void
    {
        $entry = (new TimeEntry())
            ->setDateStart(new DateTimeImmutable('2026-08-30 09:00:00'))
            ->setDateEnd(new DateTimeImmutable('2026-08-30 11:30:00'));

        // The form layer hands over plain DateTimeImmutable instances, but the
        // templates and UserActivity call Carbon-only methods on these values.
        self::assertInstanceOf(CarbonImmutable::class, $entry->getDateStart());
        self::assertInstanceOf(CarbonImmutable::class, $entry->getDateEnd());
        self::assertTrue($entry->getDateStart()->isSameAs('Y-m-d H:i', CarbonImmutable::parse('2026-08-30 09:00:00')));
    }

    public function testDateSettersAcceptNull(): void
    {
        // The manual-entry form maps an empty datetime field to null before the
        // NotNull constraint gets a chance to report it, so the setters must not
        // blow up on the way through.
        $entry = (new TimeEntry())->setDateStart(null)
            ->setDateEnd(null);

        self::assertNull($entry->getDateStart());
        self::assertNull($entry->getDateEnd());
    }

    public function testDateSettersPreserveCarbonInstancesUnchanged(): void
    {
        $start = CarbonImmutable::parse('2026-08-30 09:00:00');

        $entry = (new TimeEntry())->setDateStart($start);

        $dateStart = $entry->getDateStart();

        self::assertNotNull($dateStart);
        self::assertTrue($start->equalTo($dateStart));
    }
}
