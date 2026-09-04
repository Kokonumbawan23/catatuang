<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Validation\ValidationException;

class WalletOwnershipService
{
    public function ensureBelongsToUser(int $walletId, User $user): Wallet
    {
        $wallet = Wallet::find($walletId);

        if (! $wallet || $wallet->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'wallet_id' => 'Dompet tidak valid.',
            ]);
        }

        return $wallet;
    }
}
