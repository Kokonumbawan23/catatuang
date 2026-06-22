<x-app-layout>
    <div class="py-10 bg-gray-100 min-h-screen" x-data="{ showDeleteModal: false, deleteForm: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-xl shadow-sm border border-gray-200/60 gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Dashboard Analitik Finansial</h2>
                    <p class="text-xs text-gray-500 mt-1">Pantau ringkasan pemasukan, pengeluaran, dan manajemen dompet Anda.</p>
                </div>

                <div class="w-full sm:w-auto flex items-center space-x-3">
                    @if($wallets->isNotEmpty())
                        <form method="GET" action="{{ route('transactions.index') }}" id="wallet-switcher-form" class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Dompet:</label>
                            <x-wallet-select :wallets="$wallets" />
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 text-center">
                    <div class="p-6 hover:bg-gray-50/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 mb-2">Total Pemasukan</span>
                        <span class="block text-3xl font-extrabold text-green-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-6 hover:bg-gray-50/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 mb-2">Total Pengeluaran</span>
                        <span class="block text-3xl font-extrabold text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-6 bg-indigo-50/30">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mb-2">Saldo Dompet Saat Ini</span>
                        <span class="block text-3xl font-extrabold text-indigo-600">Rp {{ number_format($activeWalletBalance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200/60 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Catat Transaksi Baru</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Input data pemasukan atau pengeluaran secara manual.</p>
                    </div>

                    @if($wallets->isEmpty())
                        <div class="text-center py-6">
                            <p class="text-sm text-gray-500 mb-4">Anda belum memiliki dompet. Buat dompet terlebih dahulu.</p>
                            <a href="{{ route('wallets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                                + Buat Dompet
                            </a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="form_wallet" :value="__('Simpan ke Dompet')" />
                                <x-select id="form_wallet" name="wallet_id" class="mt-1">
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}" {{ $activeWallet && $activeWallet->id == $wallet->id ? 'selected' : '' }}>{{ $wallet->name }}</option>
                                    @endforeach
                                </x-select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <x-input-label for="form_type" :value="__('Tipe')" />
                                    <x-select id="form_type" name="type" class="mt-1">
                                        <option value="expense">Pengeluaran</option>
                                        <option value="income">Pemasukan</option>
                                    </x-select>
                                </div>
                                <div>
                                    <x-input-label for="amount" :value="__('Nominal (Rp)')" />
                                    <x-text-input id="amount" name="amount" type="text" inputmode="numeric" class="mt-1 block w-full text-sm idr-input" required placeholder="Rp 0" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <x-input-label for="category" :value="__('Kategori')" />
                                    <x-select id="category" name="category_id" class="mt-1">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <div>
                                    <x-input-label for="transaction_date" :value="__('Tanggal')" />
                                    <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full text-sm" required :value="now()->format('Y-m-d')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Keterangan / Catatan')" />
                                <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Catatan tambahan..."></textarea>
                            </div>

                            <x-primary-button class="w-full justify-center py-2.5 text-sm">
                                {{ __('Simpan Transaksi') }}
                            </x-primary-button>
                        </form>
                    @endif
                </div>

                <div class="lg:col-span-2 space-y-4">

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200/60">
                        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-4 text-sm">
                            <input type="hidden" name="wallet_id" value="{{ request('wallet_id', $activeWallet?->id) }}">
                            <div class="flex-1 min-w-[200px]">
                                <x-text-input id="search" name="search" type="text" class="block w-full text-sm" :value="request('search')" placeholder="Cari kata kunci deskripsi..." />
                            </div>
                            <div>
                                <x-primary-button class="py-2 px-4">
                                    {{ __('Cari') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-sm text-gray-700 uppercase tracking-wider">Log Transaksi Berjalan</h3>
                        </div>

                        <div class="overflow-x-auto">
                            @if ($transactions->isEmpty())
                                <p class="text-center text-gray-500 py-8">Belum ada transaksi bulan ini.</p>
                            @else
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50/70">
                                        <tr>
                                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Kategori</th>
                                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Keterangan</th>
                                            <th class="px-6 py-3 text-right font-semibold text-gray-600">Nominal</th>
                                            <th class="px-6 py-3 text-center font-semibold text-gray-600">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach ($transactions as $transaction)
                                            <tr class="hover:bg-gray-50/60 transition">
                                                <td class="px-6 py-4 text-gray-500 whitespace-nowrap">{{ $transaction->transaction_date->format('d M Y') }}</td>
                                                <td class="px-6 py-4 text-gray-900 font-medium">
                                                    @if ($transaction->category)
                                                        <span class="inline-flex items-center gap-1">
                                                            <span class="w-3 h-3 rounded-full inline-block" style="background-color: {{ $transaction->category->color ?? '#6366f1' }}"></span>
                                                            {{ $transaction->category->name }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-gray-500">{{ $transaction->description ?? '-' }}</td>
                                                <td class="px-6 py-4 text-right font-bold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }} whitespace-nowrap">
                                                    {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    <a href="{{ route('transactions.edit', $transaction) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                                                    <button type="button" @click="showDeleteModal = true; deleteForm = '{{ route('transactions.destroy', $transaction) }}'" class="text-red-600 hover:text-red-900 text-sm font-medium ml-3">Hapus</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="p-4 border-t border-gray-100">
                                    {{ $transactions->links() }}
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div x-show="showDeleteModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showDeleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Transaksi</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Yakin hapus transaksi ini?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <form :action="deleteForm" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Hapus
                            </button>
                        </form>
                        <button type="button" @click="showDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
