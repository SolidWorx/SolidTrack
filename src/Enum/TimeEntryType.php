<?php

namespace App\Enum;

enum TimeEntryType: string
{
    case TRACKING = 'tracking';
    case MANUAL = 'manual';
}
