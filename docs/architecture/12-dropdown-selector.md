# 12 - Custom Dropdown Selector

## Gambaran Umum

Dropdown selector menggunakan Alpine.js dengan styling Breeze, menggantikan native `<select>` browser untuk konsistensi UI dan usability yang lebih baik.

## Components

### Select Component (Generic)

```blade
{{-- resources/views/components/select.blade.php --}}
@props([
    'options' => [],
    'selected' => null,
    'placeholder' => 'Pilih...',
])

<div x-data="{
    open: false,
    selected: {{ $selected ? $selected : 'null' }},
    selectedLabel: '',
    options: {{ json_encode($options) }},

    init() {
        this.selectedLabel = this.options.find(o => o.id == this.selected)?.name || ''
    }
}">
    <input type="hidden" name="{{ $attributes->get('name') }}" :value="selected">

    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-left flex justify-between items-center"
    >
        <span x-text="selectedLabel || '{{ $placeholder }}'"></span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-transition
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg"
    >
        <template x-for="option in options" :key="option.id">
            <button
                type="button"
                @click="selected = option.id; selectedLabel = option.name; open = false"
                :class="{ 'bg-blue-50 dark:bg-blue-900': selected == option.id }"
                class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700"
                x-text="option.name"
            ></button>
        </template>
    </div>
</div>
```

### Wallet Select Component

```blade
{{-- resources/views/components/wallet-select.blade.php --}}
@props([
    'wallets' => [],
    'selected' => null,
])

<div x-data="{
    open: false,
    selected: {{ $selected ? $selected : 'null' }},
    selectedWallet: null,

    init() {
        this.selectedWallet = this.wallets?.find(w => w.id == this.selected)
    },

    selectWallet(wallet) {
        this.selected = wallet.id
        this.selectedWallet = wallet
        this.open = false
        // Update form hidden input
        document.querySelector('[name=wallet_id]').value = wallet.id
    }
}">
    <input type="hidden" name="wallet_id" value="{{ $selected }}">

    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        class="w-full bg-white dark:bg-gray-800 border border-gray-300 rounded-lg px-4 py-2 text-left flex justify-between items-center"
    >
        <span x-text="selectedWallet?.name || 'Pilih Wallet'"></span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-transition
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border rounded-lg shadow-lg"
    >
        <template x-for="wallet in wallets" :key="wallet.id">
            <button
                type="button"
                @click="selectWallet(wallet)"
                class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700"
                :class="{ 'bg-blue-50 dark:bg-blue-900': selected == wallet.id }"
            >
                <span x-text="wallet.name"></span>
            </button>
        </template>
    </div>
</div>
```

## Usage

### In Blade Views

```blade
{{-- Generic Select --}}
<x-select
    name="category_id"
    :options="$categories"
    :selected="$transaction->category_id ?? null"
    placeholder="Pilih Kategori"
/>

{{-- Wallet Select --}}
<x-wallet-select
    :wallets="$wallets"
    :selected="$transaction->wallet_id ?? null"
/>
```

### With Form Request Data

```php
// In Blade component
@props([
    'name' => '',
    'options' => [],
    'selected' => null,
])
```

## Alpine.js Dropdown Pattern

1. `x-data` contains state: `open`, `selected`, `options`
2. Hidden input stores actual value for form submission
3. `@click` toggles dropdown visibility
4. `@click.outside` closes dropdown when clicking outside
5. `x-show` with `x-transition` for smooth animation
6. Selection updates both state and hidden input

## Benefits

| Aspect | Native `<select>` | Custom Dropdown |
|--------|-------------------|-----------------|
| Styling | Limited | Full control |
| Dark mode | No | Yes |
| Icons | No | Yes |
| Search | No | Can add |
| Mobile UX | Varies | Consistent |

## Related Files

- `resources/views/components/select.blade.php`
- `resources/views/components/wallet-select.blade.php`
