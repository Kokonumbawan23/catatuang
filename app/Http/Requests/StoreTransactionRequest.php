<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'wallet_id' => ['required', 'exists:wallets,id'],
            'type' => ['required', Rule::enum(TransactionType::class)],
            'amount' => ['required', 'numeric', 'min:'.Transaction::MIN_AMOUNT, 'max:'.Transaction::MAX_AMOUNT],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
        ];

        if ($this->input('type') === TransactionType::Expense->value) {
            $rules['category_id'] = ['required', 'exists:categories,id'];
        } else {
            $rules['category_id'] = ['nullable', 'exists:categories,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'type.required' => 'Tipe transaksi wajib dipilih.',
            'type.enum' => 'Tipe transaksi harus berupa pengeluaran atau pemasukan.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Nominal minimal adalah '.Transaction::MIN_AMOUNT.'.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date' => 'Format tanggal tidak valid.',
        ];

        if ($this->input('type') === TransactionType::Expense->value) {
            $messages['category_id.required'] = 'Kategori wajib dipilih.';
        }

        return $messages;
    }
}
