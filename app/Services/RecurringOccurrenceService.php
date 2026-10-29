<?php

namespace App\Services;

use App\Enums\RecurringOccurrenceStatus;
use App\Models\RecurringOccurrence;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecurringOccurrenceService
{
    public function __construct(
        private TransactionService $transactions
    ) {}

    public function confirm(RecurringOccurrence $occurrence, User $user, float|string|null $amount = null): Transaction
    {
        $recurring = $occurrence->recurringTransaction;

        return DB::transaction(function () use ($occurrence, $user, $recurring, $amount) {
            $this->markProcessed($occurrence, RecurringOccurrenceStatus::Confirmed);

            $transaction = $this->transactions->create($user, [
                'wallet_id' => $recurring->wallet_id,
                'category_id' => $recurring->category_id,
                'type' => $recurring->type->value,
                'amount' => $amount ?? $recurring->amount,
                'description' => $recurring->title,
                'transaction_date' => $occurrence->due_date->toDateString(),
            ]);

            $occurrence->update(['transaction_id' => $transaction->id]);

            return $transaction;
        });
    }

    public function skip(RecurringOccurrence $occurrence): void
    {
        $this->markProcessed($occurrence, RecurringOccurrenceStatus::Skipped);
    }

    // Update bersyarat (status masih pending) supaya double-tap / dua tab tidak mencatat transaksi dua kali.
    private function markProcessed(RecurringOccurrence $occurrence, RecurringOccurrenceStatus $status): void
    {
        $updated = RecurringOccurrence::whereKey($occurrence->id)
            ->pending()
            ->update(['status' => $status->value]);

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'status' => 'Tagihan ini sudah diproses sebelumnya.',
            ]);
        }
    }
}
