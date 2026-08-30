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

namespace App\Time;

use Carbon\CarbonInterval;

/**
 * `CarbonInterval::hours()` builds its interval from a *string*, so the float has
 * to survive being stringified. Summing durations leaves residuals like 1.3E-7,
 * which PHP renders in scientific notation and Carbon rejects ("Invalid part
 * 1.3E-"). Durations here are only second-accurate, so rounding to whole seconds
 * is lossless and keeps the value at 0 or >= 1/3600 — always a plain decimal.
 */
final readonly class Duration
{
    public static function fromHours(float $hours): CarbonInterval
    {
        return CarbonInterval::hours(round($hours * 3600) / 3600);
    }
}
