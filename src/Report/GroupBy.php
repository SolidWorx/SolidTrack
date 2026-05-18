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

namespace App\Report;

enum GroupBy: string
{
    case Project = 'project';
    case Client = 'client';
    case Tag = 'tag';
    case Day = 'day';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Project',
            self::Client => 'Client',
            self::Tag => 'Tag',
            self::Day => 'Day',
        };
    }
}
