# 11 - Input UI Fixing

## Gambaran Umum

Perbaikan UI untuk input yang berhubungan dengan keuangan, mencakup format currency IDR, placeholder yang jelas, dan konsistensi desain.

## Fitur yang Diperbaiki

### 1. Currency Formatting (IDR)

Input keuangan otomatis diformat dengan prefix "Rp" dan pemisah ribuan:

```javascript
// resources/js/app.js
function formatCurrency(value) {
    if (!value) return ''
    const number = parseInt(value.replace(/\D/g, ''), 10)
    return 'Rp ' + number.toLocaleString('id-ID')
}

function parseCurrency(str) {
    return parseInt(str.replace(/\D/g, '') || '0', 10)
}

// Auto-format on blur
input.addEventListener('blur', function() {
    const cursorPos = this.selectionStart
    const originalLength = this.value.length
    this.value = formatCurrency(this.value)
    const newLength = this.value.length
    this.setSelectionRange(cursorPos + (newLength - originalLength), cursorPos + (newLength - originalLength))
})
```

### 2. Placeholder dengan Prefix

```html
<!-- Sebelum -->
<input placeholder="100000">

<!-- Sesudah -->
<input placeholder="Rp 100.000">
```

### 3. Consistent Colors

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

## Files Modified

| File | Perubahan |
|------|----------|
| `resources/js/app.js` | Fungsi formatCurrency IDR |
| `resources/views/transactions/create.blade.php` | Placeholder, format |
| `resources/views/transactions/edit.blade.php` | Placeholder, format |
| `resources/views/transactions/index.blade.php` | Placeholder, format |
| `resources/views/wallets/create.blade.php` | Gradient colors |
| `resources/views/wallets/edit.blade.php` | Gradient colors |
| `resources/views/wallets/index.blade.php` | Gradient colors |

## Contoh Penggunaan

```blade
{{-- Transaction Form --}}
<input
    type="text"
    name="amount"
    value="{{ old('amount') }}"
    placeholder="Rp 50.000"
    x-data="{
        value: @entangle('amount').defer,
        init() {
            this.$watch('value', v => {
                if (!v) return
                const num = parseInt(v.replace(/\D/g, ''), 10)
                this.value = 'Rp ' + num.toLocaleString('id-ID')
            })
        }
    }"
/>
```

## Related Files

- `resources/js/app.js`
- `resources/views/transactions/*.blade.php`
- `resources/views/wallets/*.blade.php`
