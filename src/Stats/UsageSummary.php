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

use App\Time\Duration;
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
            Duration::fromHours(0.0),
            Duration::fromHours(0.0),
            0.0,
            $currency,
            lastActivity: null,
        );
    }

    public function hasActivity(): bool
    {
        return $this->lastActivity instanceof CarbonImmutable;
    }
}
