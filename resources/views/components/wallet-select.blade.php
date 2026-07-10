<div x-data="{
    open: false,
    selectedWallet: null,
    wallets: [],
    init() {
        this.wallets = @js($wallets ?? []);
        const activeId = {{ request('wallet_id', $activeWallet?->id ?? 'null') }};
        this.selectedWallet = this.wallets.find(w => w.id === activeId);
    },
    selectWallet(wallet) {
        this.selectedWallet = wallet;
        this.open = false;
        document.getElementById('wallet_id_input').value = wallet.id;
        document.getElementById('wallet-switcher-form').submit();
    }
}" class="relative w-full sm:w-auto">
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-500 focus:ring-offset-1 transition-colors shadow-sm">
        <span x-text="selectedWallet ? selectedWallet.name + ' (Rp ' + new Intl.NumberFormat('id-ID').format(selectedWallet.balance) + ')' : 'Pilih Dompet'"></span>
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
        class="absolute z-50 mt-2 w-full sm:w-64 rounded-lg bg-white dark:bg-slate-800 shadow-lg border border-gray-200 dark:border-slate-700"
        style="display: none;">
        <div class="py-1 max-h-60 overflow-auto">
            <template x-for="wallet in wallets" :key="wallet.id">
                <button type="button" @click="selectWallet(wallet)"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors flex items-center justify-between"
                    :class="{'bg-indigo-50 dark:bg-indigo-900/50': selectedWallet && selectedWallet.id === wallet.id}">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white" x-text="wallet.name"></p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Saldo: Rp <span x-text="new Intl.NumberFormat('id-ID').format(wallet.balance)"></span></p>
                    </div>
                    <svg x-show="selectedWallet && selectedWallet.id === wallet.id" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>
            </template>
        </div>
    </div>
</div>
