<?php

namespace App\Enum;

enum TimeEntryStatus: string
{
    case TRACKING = 'tracking';
    case COMPLETED = 'completed';
}
