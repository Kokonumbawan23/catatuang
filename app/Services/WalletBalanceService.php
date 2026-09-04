<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Wallet;

class WalletBalanceService
{
    public function recordTransactionEffect(Wallet $wallet, TransactionType $transactionType, float|string $amount): void
    {
        $transactionType === TransactionType::Income
            ? $wallet->increment('balance', $amount)
            : $wallet->decrement('balance', $amount);
    }

    public function undoTransactionEffect(Wallet $wallet, TransactionType $transactionType, float|string $amount): void
    {
        $transactionType === TransactionType::Income
            ? $wallet->decrement('balance', $amount)
            : $wallet->increment('balance', $amount);
    }
}
