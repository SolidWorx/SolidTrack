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
}
