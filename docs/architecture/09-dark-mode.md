# 09 - Dark Mode Toggle

## Gambaran Umum

Fitur dark mode memungkinkan user beralih antara tema terang dan gelap. Preferensi disimpan di localStorage sehingga persistensi antar session.

## Implementation

### Component

```blade
{{-- resources/views/components/dark-mode-toggle.blade.php --}}
<button
    x-data="{
        darkMode: localStorage.getItem('theme') === 'dark' ||
                  (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
    }"
    x-init="
        $watch('darkMode', value => {
            if (value) {
                document.documentElement.classList.add('dark')
                localStorage.setItem('theme', 'dark')
            } else {
                document.documentElement.classList.remove('dark')
                localStorage.setItem('theme', 'light')
            }
        })
    "
    @click="darkMode = !darkMode"
    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
>
    <template x-if="darkMode">
        <svg class="w-5 h-5">...</svg>
    </template>
    <template x-if="!darkMode">
        <svg class="w-5 h-5">...</svg>
    </template>
</button>
```

### Tailwind Configuration

```js
// tailwind.config.js
module.exports = {
  darkMode: 'class',
  // ...
}
```

### Layout Integration

```blade
{{-- resources/views/layouts/app.blade.php --}}
<html :class="{ 'dark': darkMode }">
<head>
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark')
        }
    </script>
</head>
<body>
    {{ $slot }}
</body>
```

### Navigation Integration

```blade
{{-- resources/views/layouts/navigation.blade.php --}}
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <x-dark-mode-toggle />
</div>
```

## How It Works

1. **Initial Load**: Script di `<head>` cek localStorage atau system preference
2. **Toggle**: Click button toggle state `darkMode`
3. **x-watch**: Perubahan state update class `dark` di `<html>` dan simpan ke localStorage
4. **Persistence**: Theme choice bertahan di localStorage

## Related Files

- `resources/views/components/dark-mode-toggle.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `tailwind.config.js`
