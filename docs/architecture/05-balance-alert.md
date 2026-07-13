# 05 - Balance Alert & Progress Bar

## Gambaran Umum

Fitur ini memberikan visualisasi dan notifikasi ketika saldo wallet mencapai atau mendekati batas minimum yang telah ditentukan user.

## Konsep

- **balance_limit**: Batas minimum saldo yang ditentukan user per wallet
- **remaining_buffer**: Selisih antara saldo aktual dan batas minimum
- **Alert**: Muncul in-app notification saat saldo di bawah batas minimum
- **Progress Bar**: Visualisasi buffer remaining sebagai persentase

## Perhitungan

### Remaining Buffer

```
remainingBuffer = balance - balanceLimit
```

### Buffer Percentage

```
bufferPercentage = (remainingBuffer / balanceLimit) * 100
```

### Contoh Perhitungan

| Balance | Balance Limit | Remaining Buffer | Buffer % |
|---------|---------------|-----------------|----------|
| 150.000 | 100.000 | +50.000 | 50% |
| 200.000 | 100.000 | +100.000 | 100% |
| 120.000 | 100.000 | +20.000 | 20% |
| 80.000 | 100.000 | -20.000 | 0% (alert!) |

## Warna Progress Bar

| Condition | Color | Meaning |
|-----------|-------|---------|
| `buffer > 0` | 🟢 Green | Saldo di atas batas |
| `buffer >= limit * 50%` | 🟡 Yellow | Buffer >= 50% dari batas |
| `buffer < limit * 50%` | 🔴 Red | Buffer kritis, < 50% |

## Database

### Migration

```php
Schema::table('wallets', function (Blueprint $table) {
    $table->decimal('balance_limit', 15, 2)->nullable()->after('balance');
});
```

## Model Update

```php
// app/Models/Wallet.php
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
}
```

## Validation

```php
// app/Http/Requests/StoreWalletRequest.php
// app/Http/Requests/UpdateWalletRequest.php

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

## Vue Implementation

### Dashboard.vue - Progress Bar

```vue
<template>
  <div v-if="wallet.balance_limit" class="wallet-progress">
    <div class="flex justify-between text-sm mb-1">
      <span>Sisa Buffer</span>
      <span>{{ formatCurrency(remainingBuffer) }}</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
      <div
        class="h-2 rounded-full transition-all"
        :class="progressBarColorClass"
        :style="{ width: bufferPercentage + '%' }"
      ></div>
    </div>
    <div class="flex justify-between text-xs mt-1 text-gray-500">
      <span>Batas: {{ formatCurrency(wallet.balance_limit) }}</span>
      <span>{{ bufferPercentage }}%</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  wallet: Object
})

const remainingBuffer = computed(() =>
  props.wallet.balance - props.wallet.balance_limit
)

const bufferPercentage = computed(() => {
  if (!props.wallet.balance_limit) return 0
  return Math.max(0, Math.min(100,
    (remainingBuffer.value / props.wallet.balance_limit) * 100
  ))
})

const progressBarColorClass = computed(() => {
  if (remainingBuffer.value < 0) return 'bg-red-500'
  if (bufferPercentage.value < 50) return 'bg-red-400'
  if (bufferPercentage.value < 100) return 'bg-yellow-400'
  return 'bg-green-500'
})
</script>
```

### Alert Notification

```vue
<div
  v-if="alertVisible && remainingBuffer <= 0"
  class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4"
>
  <div class="flex justify-between items-center">
    <div class="flex items-center gap-2">
      <span class="text-lg">⚠️</span>
      <span>
        Saldo Dompet {{ wallet.name }} di bawah batas minimum!
      </span>
    </div>
    <button @click="dismissAlert" class="text-red-500 hover:text-red-700">
      ×
    </button>
  </div>
</div>
```

### Wallets.vue - Form Input

```vue
<!-- Create/Edit Modal -->
<div class="mb-4">
  <label class="block text-sm font-medium mb-1">
    Batas Minimum Saldo
  </label>
  <input
    v-model="form.balance_limit"
    type="text"
    placeholder="Rp 100.000"
    class="w-full px-3 py-2 border rounded-lg"
  />
  <p class="text-xs text-gray-500 mt-1">
    Opsional. Alert akan muncul saat saldo di bawah batas ini.
  </p>
</div>
```

### Wallet Card Display

```vue
<!-- Jika ada balance_limit -->
<div v-if="wallet.balance_limit" class="mt-3">
  <div class="text-xs text-gray-500 mb-1">
    Buffer: {{ formatCurrency(remainingBuffer) }}
  </div>
  <div class="w-full bg-gray-200 rounded-full h-1.5">
    <div
      class="h-1.5 rounded-full"
      :class="progressBarColorClass"
      :style="{ width: bufferPercentage + '%' }"
    ></div>
  </div>
</div>
```

## Related Files

- `database/migrations/2026_07_12_000000_add_balance_limit_to_wallets_table.php`
- `app/Models/Wallet.php`
- `app/Http/Requests/StoreWalletRequest.php`
- `app/Http/Requests/UpdateWalletRequest.php`
- `resources/js/spa/pages/Dashboard.vue`
- `resources/js/spa/pages/Wallets.vue`
