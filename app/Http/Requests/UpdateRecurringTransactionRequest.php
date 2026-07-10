<?php

namespace App\Http\Requests;

use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurringTransactionRequest extends FormRequest
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
                function ($attribute, $value, $fail) {
                    $wallet = Wallet::find($value);
                    if (! $wallet || $wallet->user_id !== $this->user()->id) {
                        $fail('Dompet tidak valid.');
                    }
                },
            ],
            'title' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:1', 'max:99999999999999'],
            'type' => ['sometimes', Rule::in(['income', 'expense'])],
            'category_id' => ['nullable', 'exists:categories,id'],
            'frequency' => ['sometimes', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'schedule_config' => ['sometimes', 'required', 'array'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
            'requires_confirmation' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('schedule_config') && $this->filled('frequency')) {
                $this->validateScheduleConfig($validator);
            } elseif ($this->filled('schedule_config') xor $this->filled('frequency')) {
                $validator->errors()->add('schedule_config', 'schedule_config dan frequency harus diisi bersamaan.');
            }
        });
    }

    protected function validateScheduleConfig($validator): void
    {
        $frequency = $this->input('frequency');
        $config = $this->input('schedule_config');

        if (! $config || ! is_array($config)) {
            return;
        }

        switch ($frequency) {
            case 'daily':
                if (! isset($config['interval_days']) || ! is_numeric($config['interval_days']) || (int) $config['interval_days'] < 1) {
                    $validator->errors()->add('schedule_config', 'Untuk frequency daily, schedule_config harus memiliki interval_days (integer >= 1).');
                }
                break;

            case 'weekly':
                if (! isset($config['day_of_week']) || ! is_array($config['day_of_week']) || empty($config['day_of_week'])) {
                    $validator->errors()->add('schedule_config', 'Untuk frequency weekly, schedule_config harus memiliki day_of_week (array dari integer 0-6).');
                } else {
                    foreach ($config['day_of_week'] as $day) {
                        if (! is_numeric($day) || (int) $day < 0 || (int) $day > 6) {
                            $validator->errors()->add('schedule_config', 'Nilai day_of_week harus integer antara 0 (Minggu) dan 6 (Sabtu).');
                            break;
                        }
                    }
                }
                break;

            case 'monthly':
                if (! isset($config['day_of_month']) || ! is_numeric($config['day_of_month']) || (int) $config['day_of_month'] < 1 || (int) $config['day_of_month'] > 31) {
                    $validator->errors()->add('schedule_config', 'Untuk frequency monthly, schedule_config harus memiliki day_of_month (integer 1-31).');
                }
                if (isset($config['interval_months']) && (! is_numeric($config['interval_months']) || (int) $config['interval_months'] < 1)) {
                    $validator->errors()->add('schedule_config', 'interval_months harus integer >= 1.');
                }
                break;

            case 'yearly':
                if (! isset($config['day_of_month']) || ! is_numeric($config['day_of_month']) || (int) $config['day_of_month'] < 1 || (int) $config['day_of_month'] > 31) {
                    $validator->errors()->add('schedule_config', 'Untuk frequency yearly, schedule_config harus memiliki day_of_month (integer 1-31).');
                }
                if (! isset($config['month_of_year']) || ! is_numeric($config['month_of_year']) || (int) $config['month_of_year'] < 1 || (int) $config['month_of_year'] > 12) {
                    $validator->errors()->add('schedule_config', 'Untuk frequency yearly, schedule_config harus memiliki month_of_year (integer 1-12).');
                }
                break;
        }
    }

    public function messages(): array
    {
        return [
            'wallet_id.exists' => 'Dompet yang dipilih tidak valid.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Nominal minimal adalah 1.',
            'type.in' => 'Tipe transaksi harus berupa income atau expense.',
            'frequency.in' => 'Frekuensi harus daily, weekly, monthly, atau yearly.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }
}
