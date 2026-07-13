<?php

namespace App\Http\Requests;

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
            'balance' => ['required', 'numeric', 'min:0', 'max:99999999999999'],
            'balance_limit' => ['nullable', 'numeric', 'min:0', 'max:99999999999999'],
        ];
    }
}
