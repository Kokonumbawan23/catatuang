<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'numeric', 'min:0', 'max:'.Transaction::MAX_AMOUNT],
            'balance_limit' => ['nullable', 'numeric', 'min:0', 'max:'.Transaction::MAX_AMOUNT],
        ];
    }
}
