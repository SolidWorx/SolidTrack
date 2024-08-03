<?php

namespace App\Twig\Extension;

use Carbon\CarbonInterval;
use Twig\Extension\AbstractExtension;
use Twig\Extension\CoreExtension;
use Twig\TwigFunction;
use function dump;

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
