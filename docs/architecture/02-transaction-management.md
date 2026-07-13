# 02 - Transaction Management

## Gambaran Umum

Sistem transaksi mengelola semua进出 keuangan user. Setiap transaksi terikat pada satu wallet dan satu kategori, dengan tipe `in` (pemasukan) atau `out` (pengeluaran).

## Struktur Database

### Migration: `transactions` table

```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['in', 'out']);
    $table->decimal('amount', 15, 2);
    $table->string('description')->nullable();
    $table->date('transaction_date');
    $table->timestamps();
});
```

## Model: Transaction

```php
class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'category_id',
        'type',
        'amount',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

## API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/transactions` | List transaksi (filterable) |
| POST | `/api/transactions` | Buat transaksi baru |
| PUT | `/api/transactions/{id}` | Update transaksi |
| DELETE | `/api/transactions/{id}` | Hapus transaksi |
| GET | `/api/transactions/export` | Export CSV |

## Validasi Input

### StoreTransactionRequest

```php
public function rules(): array
{
    return [
        'wallet_id' => 'required|exists:wallets,id',
        'category_id' => 'required|exists:categories,id',
        'type' => 'required|in:in,out',
        'amount' => 'required|numeric|gt:0', // Min: greater than 0
        'description' => 'nullable|string|max:255',
        'transaction_date' => 'required|date|before_or_equal:today',
    ];
}
```

### UpdateTransactionRequest

```php
public function rules(): array
{
    return [
        'wallet_id' => 'sometimes|required|exists:wallets,id',
        'category_id' => 'sometimes|required|exists:categories,id',
        'type' => 'sometimes|required|in:in,out',
        'amount' => 'sometimes|required|numeric|gt:0',
        'description' => 'nullable|string|max:255',
        'transaction_date' => 'sometimes|required|date|before_or_equal:today',
    ];
}
```

## Transaction Policy

```php
class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->wallet->user_id;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->wallet->user_id;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->wallet->user_id;
    }
}
```

## Balance Update Logic

Setiap membuat/edit/hapus transaksi, wallet balance di-update dalam `DB::transaction()`:

### Create Transaction

```php
DB::transaction(function () use ($request, $wallet) {
    $transaction = Transaction::create($request->validated());

    if ($transaction->type === 'in') {
        $wallet->increment('balance', $transaction->amount);
    } else {
        $wallet->decrement('balance', $transaction->amount);
    }

    return $transaction;
});
```

### Update Transaction

```php
DB::transaction(function () use ($request, $transaction, $originalWallet) {
    // Reverse original amount
    if ($transaction->type === 'in') {
        $originalWallet->decrement('balance', $transaction->amount);
    } else {
        $originalWallet->increment('balance', $transaction->amount);
    }

    // Apply new amount
    $transaction->update($request->validated());
    $newWallet = $transaction->wallet;

    if ($transaction->type === 'in') {
        $newWallet->increment('balance', $transaction->amount);
    } else {
        $newWallet->decrement('balance', $transaction->amount);
    }
});
```

### Delete Transaction

```php
DB::transaction(function () use ($transaction, $wallet) {
    if ($transaction->type === 'in') {
        $wallet->decrement('balance', $transaction->amount);
    } else {
        $wallet->increment('balance', $transaction->amount);
    }

    $transaction->delete();
});
```

## Query Filtering

Transactions bisa di-filter via query parameters:

```php
$query->when($request->wallet_id, function ($q) use ($request) {
    $q->where('wallet_id', $request->wallet_id);
})
->when($request->type, function ($q) use ($request) {
    $q->where('type', $request->type);
})
->when($request->category_id, function ($q) use ($request) {
    $q->where('category_id', $request->category_id);
})
->when($request->date_from, function ($q) use ($request) {
    $q->where('transaction_date', '>=', $request->date_from);
})
->when($request->date_to, function ($q) use ($request) {
    $q->where('transaction_date', '<=', $request->date_to);
});
```

## Category Relationship

Setiap transaksi harus memiliki category. Default categories di-seed:

| Name | Icon | Color |
|------|------|-------|
| Makanan | 🍔 | #ef4444 |
| Transportasi | 🚗 | #3b82f6 |
| Belanja | 🛒 | #10b981 |
| Hiburan | 🎬 | #8b5cf6 |
| Gaji | 💰 | #f59e0b |
| Lainnya | 📦 | #6b7280 |

## Security

1. **Amount Validation**: `amount` harus `gt:0` (greater than 0)
2. **Ownership Check**: TransactionPolicy verify user owns the wallet
3. **Wallet Isolation**: User tidak bisa add/edit/delete transaksi wallet orang lain

## Related Files

- `app/Models/Transaction.php`
- `app/Http/Controllers/Api/TransactionApiController.php`
- `app/Http/Requests/StoreTransactionRequest.php`
- `app/Http/Requests/UpdateTransactionRequest.php`
- `app/Policies/TransactionPolicy.php`
- `database/migrations/*_create_transactions_table.php`
