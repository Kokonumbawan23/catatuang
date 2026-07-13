<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }
        .income { color: #22c55e; }
        .expense { color: #ef4444; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Laporan Transaksi CatatUang</h1>
    <p class="subtitle">
        Periode: {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}
        @if ($type)
            - {{ $type === 'Income' ? 'Pemasukan' : 'Pengeluaran' }}
        @endif
    </p>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Pemasukan</div>
            <div class="summary-value income">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Pengeluaran</div>
            <div class="summary-value expense">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Saldo</div>
            <div class="summary-value">Rp {{ number_format($totalIncome - $totalExpense, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 12%">Tipe</th>
                <th style="width: 20%">Kategori</th>
                <th style="width: 15%" class="text-right">Nominal</th>
                <th style="width: 36%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $transaction)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $transaction->transaction_at->format('d/m/Y') }}</td>
                    <td>
                        <span class="{{ $transaction->type === 'Income' ? 'income' : 'expense' }}">
                            {{ $transaction->type === 'Income' ? 'Pemasukan' : 'Pengeluaran' }}
                        </span>
                    </td>
                    <td>{{ $transaction->category->name ?? 'Tanpa Kategori' }}</td>
                    <td class="text-right">
                        <span class="{{ $transaction->type === 'Income' ? 'income' : 'expense' }}">
                            {{ $transaction->type === 'Income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i:s') }} - CatatUang
    </div>
</body>
</html>