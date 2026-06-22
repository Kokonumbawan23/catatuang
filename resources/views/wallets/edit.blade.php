<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Dompet') }}
        </h2>
    </x-slot>

    <div class="py-6 px-2 sm:py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900">
                    <form method="POST" action="{{ route('wallets.update', $wallet) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <x-input-label for="name" value="Nama Dompet" />
                            <x-text-input id="name" name="name" type="text"
                                          class="mt-1 block w-full touch-manipulation"
                                          placeholder="Contoh: Dompet Pribadi, BCA, Dana..."
                                          value="{{ $wallet->name }}"
                                          required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="balance" value="Saldo Saat Ini (Rp)" />
                            <x-text-input id="balance" name="balance" type="text" inputmode="numeric"
                                          class="mt-1 block w-full touch-manipulation"
                                          placeholder="Rp 0"
                                          value="{{ $wallet->balance }}"
                                          required />
                            <x-input-error :messages="$errors->get('balance')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Perubahan saldo di sini akan mempengaruhi perhitungan saldo dompet.</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <x-primary-button class="justify-center touch-manipulation">{{ __('Update') }}</x-primary-button>
                            <a href="{{ route('wallets.index') }}"
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
