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

namespace App\Twig\Extension;

use App\Time\Duration;
use Carbon\CarbonInterval;
use Twig\Attribute\AsTwigFunction;

final class DateIntervalExtension
{
    #[AsTwigFunction(name: 'format_interval')]
    public function formatDateInterval(CarbonInterval $interval, bool $humanReadable = true): string
    {
        $interval = Duration::fromHours($interval->totalHours);

        if ($humanReadable) {
            return $interval->forHumans(short: true, parts: 3);
        }

        $format = '%I:%S';

        if ($interval->totalHours > 0) {
            $format = '%H:' . $format;
        }

        return $interval->format($format);
    }
}
