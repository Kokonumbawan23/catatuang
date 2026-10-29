<?php

namespace Database\Factories;

use App\Models\RecurringOccurrence;
use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringOccurrenceFactory extends Factory
{
    protected $model = RecurringOccurrence::class;

    public function definition(): array
    {
        return [
            'recurring_transaction_id' => RecurringTransaction::factory(),
            'due_date' => now()->toDateString(),
            'status' => 'pending',
            'transaction_id' => null,
        ];
    }
}
