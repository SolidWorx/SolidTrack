<?php

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Twig\Extension;

use Carbon\CarbonInterval;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DateIntervalExtension extends AbstractExtension
{
    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('format_interval', $this->formatDateInterval(...)),
        ];
    }

    private function formatDateInterval(CarbonInterval $interval, bool $humanReadable = true): string
    {
        $interval = CarbonInterval::hours($interval->totalHours);

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
