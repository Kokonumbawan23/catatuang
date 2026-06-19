<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dompet Saya') }}
            </h2>
            <a href="{{ route('wallets.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center text-center">
                + Tambah Dompet
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($wallets->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada dompet</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat dompet pertama Anda.</p>
                            <div class="mt-6">
                                <a href="{{ route('wallets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    + Buat Dompet
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($wallets as $wallet)
                                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-indigo-100 font-medium">Saldo</p>
                                            <p class="text-2xl font-bold mt-1">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</p>
                                        </div>
                                        <svg class="w-8 h-8 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div class="mt-6">
                                        <p class="text-lg font-semibold">{{ $wallet->name }}</p>
                                        <p class="text-xs text-indigo-200 mt-1">{{ $wallet->transactions_count }} transaksi</p>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-indigo-400/30 flex justify-end gap-3">
                                        <a href="{{ route('wallets.edit', $wallet) }}" class="text-sm text-indigo-100 hover:text-white font-medium">Edit</a>
                                        <form action="{{ route('wallets.destroy', $wallet) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus dompet ini? Semua transaksi terkait juga akan terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-indigo-100 hover:text-white font-medium">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
