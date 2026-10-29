<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmRecurringOccurrenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['nullable', 'numeric', 'min:'.Transaction::MIN_AMOUNT, 'max:'.Transaction::MAX_AMOUNT],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Nominal minimal adalah '.Transaction::MIN_AMOUNT.'.',
        ];
    }
}
