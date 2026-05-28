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

namespace App\Test\Enum;

use App\Enum\StatsPeriod;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatsPeriod::class)]
final class StatsPeriodTest extends TestCase
{
    public function testAllTimeHasNoRange(): void
    {
        $now = CarbonImmutable::parse('2026-05-28 14:00:00');

        self::assertNull(StatsPeriod::AllTime->range($now));
    }

    public function testMonthRangeCoversCurrentCalendarMonth(): void
    {
        $now = CarbonImmutable::parse('2026-05-28 14:00:00');

        $range = StatsPeriod::Month->range($now);
        self::assertNotNull($range);
        [$from, $to] = $range;

        self::assertSame('2026-05-01 00:00:00', $from->format('Y-m-d H:i:s'));
        self::assertSame('2026-05-31 23:59:59', $to->format('Y-m-d H:i:s'));
    }

    public function testYearRangeCoversCurrentCalendarYear(): void
    {
        $now = CarbonImmutable::parse('2026-05-28 14:00:00');

        $range = StatsPeriod::Year->range($now);
        self::assertNotNull($range);
        [$from, $to] = $range;

        self::assertSame('2026-01-01 00:00:00', $from->format('Y-m-d H:i:s'));
        self::assertSame('2026-12-31 23:59:59', $to->format('Y-m-d H:i:s'));
    }

    public function testLabels(): void
    {
        self::assertSame('All time', StatsPeriod::AllTime->label());
        self::assertSame('This month', StatsPeriod::Month->label());
        self::assertSame('This year', StatsPeriod::Year->label());
    }
}
