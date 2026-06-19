<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-6 px-2 sm:py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="wallet_id" value="Dompet" />
                            <select id="wallet_id" name="wallet_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 touch-manipulation"
                                    required>
                                <option value="">Pilih Dompet</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}" {{ old('wallet_id') == $wallet->id ? 'selected' : '' }}>
                                        {{ $wallet->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('wallet_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="type" value="Tipe Transaksi" />
                            <select id="type" name="type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 touch-manipulation"
                                    required>
                                <option value="">Pilih Tipe</option>
                                <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Pemasukan</option>
                                <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="category_id" value="Kategori" />
                            <select id="category_id" name="category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 touch-manipulation">
                                <option value="">Pilih Kategori (opsional)</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="amount" value="Nominal (Rp)" />
                            <x-text-input id="amount" name="amount" type="number" step="1" min="1"
                                          class="mt-1 block w-full touch-manipulation"
                                          placeholder="50000"
                                          value="{{ old('amount') }}"
                                          required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="transaction_date" value="Tanggal" />
                            <x-text-input id="transaction_date" name="transaction_date" type="date"
                                          class="mt-1 block w-full touch-manipulation"
                                          value="{{ old('transaction_date', now()->format('Y-m-d')) }}"
                                          max="{{ now()->format('Y-m-d') }}"
                                          required />
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" value="Catatan (opsional)" />
                            <textarea id="description" name="description" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 touch-manipulation"
                                      placeholder="Gaji bulanan">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <x-primary-button class="justify-center touch-manipulation">{{ __('Simpan') }}</x-primary-button>
                            <a href="{{ route('transactions.index') }}"
                               class="inline-flex items-center justify-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 touch-manipulation">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
