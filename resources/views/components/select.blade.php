<div x-data="{
    open: false,
    selected: null,
    options: [],
    name: '',
    init() {
        const root = this.$root;
        this.name = root.getAttribute('data-name');
        this.options = JSON.parse(root.getAttribute('data-options') || '[]');
        const val = root.getAttribute('data-value') || null;
        this.selected = this.options.find(o => String(o.value) === String(val)) || this.options[0] || null;
    },
    select(option) {
        this.selected = option;
        this.open = false;
        document.querySelector(`[data-select-name='${this.name}']`).value = option.value;
    }
}" class="relative w-full"
data-name="{{ $attributes->get('name') }}"
data-value="{{ $attributes->get('value') ?? old($attributes->get('name')) }}"
data-options="{!! htmlspecialchars(json_encode($options ?? []), ENT_QUOTES, 'UTF-8') !!}">
    <input type="hidden" name="{{ $attributes->get('name') }}" data-select-name="{{ $attributes->get('name') }}" x-bind:value="selected?.value">

    <button type="button" @click="open = !open"
        class="w-full inline-flex items-center justify-between gap-2 px-3 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-500 focus:ring-offset-1 transition-colors shadow-sm">
        <span x-text="selected ? selected.label : 'Pilih'"></span>
        <svg class="w-4 h-4 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="absolute z-50 mt-2 w-full rounded-lg bg-white dark:bg-slate-800 shadow-lg border border-gray-200 dark:border-slate-700"
        style="display: none;">
        <div class="py-1 max-h-60 overflow-auto">
            <template x-for="option in options" :key="option.value">
                <button type="button" @click="select(option)"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors flex items-center justify-between"
                    :class="{'bg-indigo-50 dark:bg-indigo-900/50': selected && String(selected.value) === String(option.value)}">
                    <span class="font-medium text-gray-900 dark:text-white" x-text="option.label"></span>
                    <svg x-show="selected && String(selected.value) === String(option.value)" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>
            </template>
        </div>
    </div>
</div>
