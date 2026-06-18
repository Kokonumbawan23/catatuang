<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Pengeluaran') }}
        </h2>
    </x-slot>

    <div class="py-6 px-2 sm:py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900">
                    <form method="POST" action="{{ route('expenses.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="category_id" value="Kategori" />
                            <select id="category_id" name="category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 touch-manipulation"
                                    required>
                                <option value="">Pilih Kategori</option>
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
                            <x-input-label for="spent_at" value="Tanggal" />
                            <x-text-input id="spent_at" name="spent_at" type="date"
                                          class="mt-1 block w-full touch-manipulation"
                                          value="{{ old('spent_at', now()->format('Y-m-d')) }}"
                                          max="{{ now()->format('Y-m-d') }}"
                                          required />
                            <x-input-error :messages="$errors->get('spent_at')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" value="Catatan (opsional)" />
                            <textarea id="description" name="description" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 touch-manipulation"
                                      placeholder="Makan siang di kantin">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <x-primary-button class="justify-center touch-manipulation">{{ __('Simpan') }}</x-primary-button>
                            <a href="{{ route('expenses.index') }}"
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
