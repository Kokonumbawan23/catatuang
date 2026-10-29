<?php

namespace App\Policies;

use App\Models\RecurringOccurrence;
use App\Models\User;

class RecurringOccurrencePolicy
{
    public function update(User $user, RecurringOccurrence $recurringOccurrence): bool
    {
        return $user->id === $recurringOccurrence->recurringTransaction->user_id;
    }
}
