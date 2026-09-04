<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TransactionExportService
{
    private const CSV_HEADER_ROW = ['Tanggal', 'Dompet', 'Kategori', 'Tipe', 'Nominal', 'Keterangan'];

    public function fetchForExport(User $user, int $month, int $year, ?int $walletId, ?TransactionType $type = null): Collection
    {
        $query = $user->transactions()
            ->with(['category', 'wallet'])
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year);

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    public function toCsvRows(Collection $transactions): array
    {
        $rows = [self::CSV_HEADER_ROW];

        foreach ($transactions as $transaction) {
            $rows[] = [
                $transaction->transaction_date->format('Y-m-d'),
                $transaction->wallet->name ?? '-',
                $transaction->category->name ?? '-',
                $transaction->type === TransactionType::Income ? 'Pemasukan' : 'Pengeluaran',
                number_format($transaction->amount, 0, ',', '.'),
                $transaction->description ?? '',
            ];
        }

        return $rows;
    }

    public function csvFilename(int $year, int $month): string
    {
        return "transactions_{$year}_{$month}.csv";
    }
}
