<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_id' => [
                'sometimes',
                'exists:wallets,id',
            ],
            'type' => ['sometimes', Rule::in(['expense', 'income'])],
            'category_id' => [
                'sometimes',
                'exists:categories,id',
            ],
            'amount' => ['sometimes', 'numeric', 'min:1', 'max:99999999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['sometimes', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Tipe transaksi harus berupa pengeluaran atau pemasukan.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Nominal minimal adalah 1.',
            'transaction_date.date' => 'Format tanggal tidak valid.',
        ];
    }
}
