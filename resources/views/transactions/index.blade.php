<x-app-layout>
    <div class="py-10 bg-gray-100 min-h-screen">
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
                            <label for="wallet_context" class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Dompet:</label>
                            <select id="wallet_context" name="wallet_id" onchange="document.getElementById('wallet-switcher-form').submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-gray-50">
                                @foreach($wallets as $wallet)
                                    <option value="{{ $wallet->id }}" {{ request('wallet_id', $activeWallet?->id) == $wallet->id ? 'selected' : '' }}>
                                        {{ $wallet->name }} (Rp {{ number_format($wallet->balance, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
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
                                <select id="form_wallet" name="wallet_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @foreach($wallets as $wallet)
                                        <option value="{{ $wallet->id }}" {{ $activeWallet && $activeWallet->id == $wallet->id ? 'selected' : '' }}>{{ $wallet->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <x-input-label for="form_type" :value="__('Tipe')" />
                                    <select id="form_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="expense">Pengeluaran</option>
                                        <option value="income">Pemasukan</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="amount" :value="__('Nominal (Rp)')" />
                                    <x-text-input id="amount" name="amount" type="number" class="mt-1 block w-full text-sm" required placeholder="0" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <x-input-label for="category" :value="__('Kategori')" />
                                    <select id="category" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
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
                                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline ml-3" onsubmit="return confirm('Yakin hapus?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Hapus</button>
                                                    </form>
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
    </div>
</x-app-layout>
