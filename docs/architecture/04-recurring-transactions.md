# 04 - Recurring Transactions

## Gambaran Umum

Fitur recurring transactions memungkinkan user membuat transaksi yang otomatis diproses ulang secara berkala (harian, mingguan, bulanan, tahunan) tanpa perlu input manual setiap kali.

## Struktur Database

### Migration: `recurring_transactions` table

```php
Schema::create('recurring_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['in', 'out']);
    $table->decimal('amount', 15, 2);
    $table->string('description')->nullable();
    $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
    $table->json('schedule_config')->nullable(); // Custom schedule
    $table->boolean('is_active')->default(true);
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->date('last_processed_at')->nullable();
    $table->timestamps();
});
```

## Schedule Config

`schedule_config` adalah JSON untuk custom schedule:

```json
// Monthly - setiap tanggal 25
{
  "day_of_month": 25
}

// Weekly - setiap hari Senin
{
  "day_of_week": 1
}

// Daily - tidak perlu config
{}
```

## Model: RecurringTransaction

```php
class RecurringTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'category_id',
        'type',
        'amount',
        'description',
        'frequency',
        'schedule_config',
        'is_active',
        'start_date',
        'end_date',
        'last_processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'schedule_config' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_processed_at' => 'datetime',
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

## Schedule Service

`RecurringTransactionScheduleService` menentukan tanggal下次 execution:

```php
class RecurringTransactionScheduleService
{
    public function getNextRunDate(RecurringTransaction $recurring): ?Carbon
    {
        $last = $recurring->last_processed_at
            ? Carbon::parse($recurring->last_processed_at)
            : Carbon::parse($recurring->start_date);

        return match ($recurring->frequency) {
            'daily' => $last->addDay(),
            'weekly' => $last->addWeek(),
            'monthly' => $this->getNextMonthly($recurring, $last),
            'yearly' => $last->addYear(),
        };
    }

    private function getNextMonthly(RecurringTransaction $recurring, Carbon $last): Carbon
    {
        $config = $recurring->schedule_config ?? [];
        $dayOfMonth = $config['day_of_month'] ?? $last->day;

        return $last->copy()->addMonth()->day($dayOfMonth);
    }
}
```

## API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/recurring-transactions` | List recurring |
| POST | `/api/recurring-transactions` | Buat recurring |
| GET | `/api/recurring-transactions/{id}` | Detail recurring |
| PUT | `/api/recurring-transactions/{id}` | Update recurring |
| DELETE | `/api/recurring-transactions/{id}` | Hapus recurring |
| PATCH | `/api/recurring-transactions/{id}/toggle` | Toggle active |

## Artisan Command

### ProcessRecurringTransactions

Jalankan setiap menit via scheduler:

```bash
# Manual
php artisan recurring:process

# Setup di Kernel.php (production)
$schedule->command('recurring:process')->everyMinute();
```

### Logic Flow

```php
// app/Console/Commands/ProcessRecurringTransactions.php

$recurring = RecurringTransaction::where('is_active', true)
    ->where('start_date', '<=', now())
    ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
    ->where(function ($q) {
        $q->whereNull('last_processed_at')
          ->orWhere('last_processed_at', '<', $nextRunDate);
    })
    ->get();

foreach ($recurring as $rt) {
    DB::transaction(function () use ($rt) {
        // Create actual transaction
        Transaction::create([...]);

        // Update wallet balance
        if ($rt->type === 'in') {
            $rt->wallet->increment('balance', $rt->amount);
        } else {
            $rt->wallet->decrement('balance', $rt->amount);
        }

        // Mark as processed
        $rt->update(['last_processed_at' => now()]);
    });
}
```

## Validation

### StoreRecurringTransactionRequest

```php
public function rules(): array
{
    return [
        'wallet_id' => 'required|exists:wallets,id',
        'category_id' => 'required|exists:categories,id',
        'type' => 'required|in:in,out',
        'amount' => 'required|numeric|gt:0',
        'description' => 'nullable|string|max:255',
        'frequency' => 'required|in:daily,weekly,monthly,yearly',
        'schedule_config' => 'nullable|array',
        'is_active' => 'boolean',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'nullable|date|after:start_date',
    ];
}
```

## Policy

```php
class RecurringTransactionPolicy
{
    public function view(User $user, RecurringTransaction $rt): bool
    {
        return $user->id === $rt->wallet->user_id;
    }

    public function update(User $user, RecurringTransaction $rt): bool
    {
        return $user->id === $rt->wallet->user_id;
    }

    public function delete(User $user, RecurringTransaction $rt): bool
    {
        return $user->id === $rt->wallet->user_id;
    }
}
```

## Related Files

- `app/Models/RecurringTransaction.php`
- `app/Services/RecurringTransactionScheduleService.php`
- `app/Console/Commands/ProcessRecurringTransactions.php`
- `app/Http/Controllers/Api/RecurringTransactionApiController.php`
- `app/Policies/RecurringTransactionPolicy.php`
- `database/migrations/*_create_recurring_transactions_table.php`
