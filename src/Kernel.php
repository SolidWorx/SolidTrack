<?php

/*
 * This file is part of {PROJECT_NAME} project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App;

use Carbon\CarbonInterval;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    public const APP_VERSION = '0.1.0-dev';

    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();

        date_default_timezone_set('UTC');
        mb_internal_encoding('UTF-8');
        ini_set('intl.default_locale', 'en_US');

        CarbonInterval::enableFloatSetters();
    }
}
