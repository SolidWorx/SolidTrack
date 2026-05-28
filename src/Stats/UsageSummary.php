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

namespace App\Stats;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;

final readonly class UsageSummary
{
    public function __construct(
        public CarbonInterval $totalDuration,
        public CarbonInterval $billableDuration,
        public float $amount,
        public ?string $currency,
        public ?CarbonImmutable $lastActivity,
    ) {
    }

    public static function empty(?string $currency = null): self
    {
        return new self(
            CarbonInterval::hours(0.0),
            CarbonInterval::hours(0.0),
            0.0,
            $currency,
            null,
        );
    }

    public function hasActivity(): bool
    {
        return $this->lastActivity !== null;
    }
}
