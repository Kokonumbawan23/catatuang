<?php

namespace App\Http\Requests;

use App\Models\Category;
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
        return [
            'type' => ['required', Rule::in(['Expense', 'Income'])],
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = Category::find($value);
                    $type = $this->input('type');

                    if ($category && $type === 'Expense' && ! $category->isExpenseAllowed()) {
                        $fail('Kategori ini tidak diperbolehkan untuk pengeluaran.');
                    }
                    if ($category && $type === 'Income' && ! $category->isIncomeAllowed()) {
                        $fail('Kategori ini tidak diperbolehkan untuk pemasukan.');
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_at' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe transaksi wajib dipilih.',
            'type.in' => 'Tipe transaksi harus berupa Pengeluaran atau Pemasukan.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Nominal minimal adalah 1.',
            'transaction_at.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_at.date' => 'Format tanggal tidak valid.',
        ];
    }
}
