# 08 - Export CSV

## Gambaran Umum

Fitur export memungkinkan user mengunduh data transaksi dalam format CSV untuk keperluan analisis atau backup.

## API Endpoint

### GET `/api/transactions/export`

Returns CSV file dengan header `Content-Type: text/csv`

**Query Parameters:**

| Parameter | Required | Description |
|-----------|----------|-------------|
| wallet_id | No | Filter by wallet |
| type | No | Filter by type (in/out) |
| date_from | No | Start date (YYYY-MM-DD) |
| date_to | No | End date (YYYY-MM-DD) |

## Implementation

### Controller

```php
class ExporterController extends Controller
{
    public function export(Request $request)
    {
        $query = Transaction::with('category', 'wallet')
            ->whereHas('wallet', function ($q) {
                $q->where('user_id', auth()->id());
            });

        // Apply filters
        $query->when($request->wallet_id, fn($q) => $q->where('wallet_id', $request->wallet_id));
        $query->when($request->type, fn($q) => $q->where('type', $request->type));
        $query->when($request->date_from, fn($q) => $q->where('transaction_date', '>=', $request->date_from));
        $query->when($request->date_to, fn($q) => $q->where('transaction_date', '<=', $request->date_to));

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        return $this->generateCsv($transactions);
    }

    private function generateCsv($transactions)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // CSV Header
            fputcsv($handle, [
                'Date',
                'Wallet',
                'Category',
                'Type',
                'Amount',
                'Description',
            ]);

            // CSV Rows
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->transaction_date->format('Y-m-d'),
                    $t->wallet->name,
                    $t->category->name,
                    $t->type,
                    $t->amount,
                    $t->description ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
```

## CSV Format

```csv
Date,Wallet,Category,Type,Amount,Description
2026-07-12,Dompet Utama,Makanan,out,50000,Makan siang
2026-07-11,Dompet Utama,Gaji,in,5000000,Gaji bulan Juli
2026-07-10,Kartu Debit,Transportasi,out,25000,Grab ke kantor
```

## Vue SPA Implementation

### Transactions.vue - Export Button

Fitur export CSV sudah terintegrasi di halaman Transactions Vue SPA.

**Lokasi di template:**
- Tombol Export CSV ada di sebelah tombol Cari pada search bar

**Fungsi di script:**

```javascript
const exportTransactions = async () => {
    try {
        const params = {
            month: selectedMonth.value,
            year: selectedYear.value,
        };
        if (selectedWalletId.value) {
            params.wallet_id = selectedWalletId.value;
        }
        const response = await axios.get('/api/transactions/export', {
            params,
            responseType: 'blob',
        });
        const blob = new Blob([response.data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Gagal export:', error);
        alert('Gagal export transaksi.');
    }
};
```

**Filter yang digunakan:**
- `wallet_id` - dari dropdown wallet yang dipilih
- `month` - dari dropdown bulan
- `year` - dari dropdown tahun

## Security

- User hanya bisa export transaksi miliknya sendiri (via `whereHas('wallet', ...)`)
- Wallet isolation enforced di query level
- Token authentication required

## Related Files

- `app/Http/Controllers/ExporterController.php`
- `routes/web.php` (route ke exporter)
- `resources/js/spa/pages/Transactions.vue` (Export button + fungsi exportTransactions)
