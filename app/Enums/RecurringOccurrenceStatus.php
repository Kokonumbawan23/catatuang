<?php

namespace App\Enums;

enum RecurringOccurrenceStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Skipped = 'skipped';
}
