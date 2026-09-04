<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Jobs\SendBalanceLimitAlert;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private WalletOwnershipService $walletOwnership,
        private WalletBalanceService $walletBalance,
        private ActivityLogger $logger
    ) {}

    public function create(User $user, array $attributes): Transaction
    {
        $targetWallet = $this->walletOwnership->ensureBelongsToUser($attributes['wallet_id'], $user);
        $transactionType = TransactionType::from($attributes['type']);

        $transaction = DB::transaction(function () use ($attributes, $user, $targetWallet, $transactionType) {
            $transaction = Transaction::create([...$attributes, 'user_id' => $user->id]);

            $this->walletBalance->recordTransactionEffect($targetWallet, $transactionType, $attributes['amount']);

            $this->logger->transactionCreated(
                $user->id,
                $transaction->id,
                $transactionType,
                $attributes['amount'],
                $targetWallet->id
            );

            return $transaction;
        });

        SendBalanceLimitAlert::dispatch($targetWallet);

        return $transaction;
    }

    public function update(Transaction $transaction, User $user, array $attributes): Transaction
    {
        $walletId = $attributes['wallet_id'] ?? $transaction->wallet_id;
        $transactionType = isset($attributes['type']) ? TransactionType::from($attributes['type']) : $transaction->type;
        $amount = $attributes['amount'] ?? $transaction->amount;

        $attributes['wallet_id'] = $walletId;
        $attributes['type'] = $transactionType;
        $attributes['amount'] = $amount;

        $targetWallet = $this->walletOwnership->ensureBelongsToUser($walletId, $user);
        $currentWallet = $transaction->wallet;
        $walletChanged = $currentWallet->id !== $targetWallet->id;

        DB::transaction(function () use ($transaction, $attributes, $user, $currentWallet, $targetWallet, $transactionType, $amount) {
            $this->walletBalance->undoTransactionEffect($currentWallet, $transaction->type, $transaction->amount);

            $transaction->update($attributes);

            $this->walletBalance->recordTransactionEffect($targetWallet, $transactionType, $amount);

            $this->logger->transactionUpdated($user->id, $transaction->id);
        });

        SendBalanceLimitAlert::dispatch($currentWallet);

        if ($walletChanged) {
            SendBalanceLimitAlert::dispatch($targetWallet);
        }

        return $transaction;
    }

    public function delete(Transaction $transaction, User $user): void
    {
        $wallet = $transaction->wallet;

        DB::transaction(function () use ($transaction, $wallet) {
            $this->walletBalance->undoTransactionEffect($wallet, $transaction->type, $transaction->amount);

            $transaction->delete();
        });

        $this->logger->transactionDeleted($user->id, $transaction->id);

        SendBalanceLimitAlert::dispatch($wallet);
    }
}
