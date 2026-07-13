# 01 - Multi-Wallet Management

## Gambaran Umum

Sistem multi-wallet memungkinkan user membuat dan mengelola多个 dompet digital untuk memisahkan keuangan mereka. Setiap wallet memiliki balance sendiri yang diperbarui secara otomatis setiap kali transaksi dibuat.

## Struktur Database

### Migration: `wallets` table

```php
Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('icon')->nullable();
    $table->string('color')->nullable();
    $table->decimal('balance', 15, 2)->default(0);
    $table->decimal('balance_limit', 15, 2)->nullable(); // FEAT-BALANCE-ALERT
    $table->timestamps();
});
```

## Model: Wallet

```php
class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'balance',
        'balance_limit', // FEAT-BALANCE-ALERT
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }
}
```

## API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/wallets` | List semua wallet user |
| POST | `/api/wallets` | Buat wallet baru |
| GET | `/api/wallets/{id}` | Detail wallet |
| PUT | `/api/wallets/{id}` | Update wallet |
| DELETE | `/api/wallets/{id}` | Hapus wallet |

## Validasi Input

### StoreWalletRequest

```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'icon' => 'nullable|string|max:50',
        'color' => 'nullable|string|max:30|regex:/^#[0-9A-Fa-f]{6}$/',
        'balance_limit' => 'nullable|numeric|min:0', // FEAT-BALANCE-ALERT
    ];
}
```

### UpdateWalletRequest

```php
public function rules(): array
{
    return [
        'name' => 'sometimes|required|string|max:255',
        'icon' => 'nullable|string|max:50',
        'color' => 'nullable|string|max:30|regex:/^#[0-9A-Fa-f]{6}$/',
        'balance_limit' => 'nullable|numeric|min:0', // FEAT-BALANCE-ALERT
    ];
}
```

## Wallet Policy

`WalletPolicy` memastikan user hanya bisa mengakses wallet miliknya sendiri:

```php
class WalletPolicy
{
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }

    public function update(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }

    public function delete(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }
}
```

Policy di-apply ke semua routes resource:

```php
Route::resource('wallets', WalletController::class)->middleware(['auth:sanctum', 'can:view,wallet']);
```

## Balance Calculation

Balance di-update setiap kali transaksi dibuat/diedit/dihapus menggunakan `DB::transaction()`:

```php
DB::transaction(function () use ($wallet, $amount, $type) {
    if ($type === 'in') {
        $wallet->increment('balance', $amount);
    } else {
        $wallet->decrement('balance', $amount);
    }
});
```

## Balance Limit (FEAT-BALANCE-ALERT)

Setiap wallet bisa memiliki `balance_limit` opsional untuk alert:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| balance_limit | decimal(15,2) nullable | Batas minimum saldo |

Lihat [05-balance-alert.md](./05-balance-alert.md) untuk detail implementasi alert.

## Color Palette

Wallet cards menggunakan gradient warna yang bervariasi:

```php
$colors = [
    ['from' => '#06b6d4', 'to' => '#3b82f6'], // cyan - blue
    ['from' => '#8b5cf6', 'to' => '#ec4899'], // purple - pink
    ['from' => '#10b981', 'to' => '#3b82f6'], // emerald - blue
    ['from' => '#f59e0b', 'to' => '#ef4444'], // amber - red
    ['from' => '#6366f1', 'to' => '#8b5cf6'], // indigo - purple
];
```

## Related Files

- `app/Models/Wallet.php`
- `app/Http/Controllers/Api/WalletController.php`
- `app/Http/Requests/StoreWalletRequest.php`
- `app/Http/Requests/UpdateWalletRequest.php`
- `app/Policies/WalletPolicy.php`
- `database/migrations/*_create_wallets_table.php`
