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
        $type = $this->input('type', $this->route('transaction')?->type);

        $rules = [
            'wallet_id' => [
                'sometimes',
                'exists:wallets,id',
            ],
            'type' => ['sometimes', Rule::in(['expense', 'income'])],
            'amount' => ['sometimes', 'numeric', 'min:1', 'max:99999999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['sometimes', 'date', 'before_or_equal:today'],
        ];

        if ($type === 'expense') {
            $rules['category_id'] = ['sometimes', 'required', 'exists:categories,id'];
        } else {
            $rules['category_id'] = ['nullable', 'exists:categories,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'type.in' => 'Tipe transaksi harus berupa pengeluaran atau pemasukan.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Nominal minimal adalah 1.',
            'transaction_date.date' => 'Format tanggal tidak valid.',
        ];

        if ($this->input('type') === 'expense') {
            $messages['category_id.required'] = 'Kategori wajib dipilih.';
        }

        return $messages;
    }
}
