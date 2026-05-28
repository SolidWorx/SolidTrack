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

namespace App\Enum;

use Carbon\CarbonImmutable;

enum StatsPeriod: string
{
    case AllTime = 'all';
    case Month = 'month';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::AllTime => 'All time',
            self::Month => 'This month',
            self::Year => 'This year',
        };
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null Null means "no date filter" (all time).
     */
    public function range(CarbonImmutable $now): ?array
    {
        return match ($this) {
            self::AllTime => null,
            self::Month => [$now->startOfMonth(), $now->endOfMonth()],
            self::Year => [$now->startOfYear(), $now->endOfYear()],
        };
    }
}
