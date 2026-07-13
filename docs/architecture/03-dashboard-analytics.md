# 03 - Dashboard & Analytics

## Gambaran Umum

Dashboard menampilkan ringkasan finansial dengan 3 kolom utama: Total Pemasukan, Total Pengeluaran, dan Saldo. Data berubah secara dinamis berdasarkan wallet yang dipilih.

## API Endpoint

### GET `/api/dashboard`

Returns summary data untuk user yang sedang login:

```json
{
  "in": 1500000,
  "out": 750000,
  "balance": 750000,
  "wallet_name": "Dompet Utama",
  "recent_transactions": [
    {
      "id": 1,
      "type": "in",
      "amount": "500000.00",
      "description": "Gaji",
      "transaction_date": "2026-07-12",
      "category": {
        "id": 5,
        "name": "Gaji",
        "icon": "💰",
        "color": "#f59e0b"
      }
    }
  ]
}
```

## Perhitungan

### Summary (In, Out, Balance)

```php
$in = Transaction::where('wallet_id', $walletId)
    ->where('type', 'in')
    ->whereMonth('transaction_date', $month)
    ->whereYear('transaction_date', $year)
    ->sum('amount');

$out = Transaction::where('wallet_id', $walletId)
    ->where('type', 'out')
    ->whereMonth('transaction_date', $month)
    ->whereYear('transaction_date', $year)
    ->sum('amount');

$balance = $wallet->balance; // From wallet table
```

### Recent Transactions

Ambil 10 transaksi terakhir dengan relasi category:

```php
$recentTransactions = Transaction::with('category')
    ->where('wallet_id', $walletId)
    ->orderBy('transaction_date', 'desc')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

## Vue Component Structure

```
Dashboard.vue
├── Wallet Selector (dropdown)
├── Summary Cards (3-col grid)
│   ├── In Card (green)
│   ├── Out Card (red)
│   └── Balance Card (blue)
├── Recent Transactions Table
└── Balance Alert Notification (conditional)
```

## State Management (Pinia)

```javascript
// stores/dashboard.js
export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    summary: { in: 0, out: 0, balance: 0 },
    walletName: '',
    recentTransactions: [],
    selectedWalletId: null
  }),

  actions: {
    async fetchDashboard(walletId) {
      // API call to /api/dashboard?wallet_id=xxx
    }
  }
});
```

## Balance Alert Integration

Dashboard juga menampilkan alert jika balance di bawah `balance_limit`:

```vue
<div v-if="alertVisible" class="balance-alert">
  <span>⚠️ Saldo Dompet {{ walletName }} di bawah batas minimum!</span>
  <button @click="dismissAlert">×</button>
</div>
```

Lihat [05-balance-alert.md](./05-balance-alert.md) untuk detail implementasi alert.

## Related Files

- `app/Http/Controllers/Api/DashboardApiController.php`
- `resources/js/spa/pages/Dashboard.vue`
- `resources/js/spa/stores/dashboard.js`
