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

    <div class="py-6 sm:py-12" x-data="{ showDeleteModal: false, deleteForm: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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
                            @foreach ($wallets as $index => $wallet)
                                @php
                                    $gradients = [
                                        ['from' => 'from-emerald-500', 'to' => 'to-teal-600', 'text' => 'text-emerald-100', 'border' => 'border-emerald-400/30'],
                                        ['from' => 'from-blue-500', 'to' => 'to-cyan-600', 'text' => 'text-blue-100', 'border' => 'border-blue-400/30'],
                                        ['from' => 'from-purple-500', 'to' => 'to-pink-600', 'text' => 'text-purple-100', 'border' => 'border-purple-400/30'],
                                        ['from' => 'from-orange-500', 'to' => 'to-red-600', 'text' => 'text-orange-100', 'border' => 'border-orange-400/30'],
                                        ['from' => 'from-indigo-500', 'to' => 'to-purple-600', 'text' => 'text-indigo-100', 'border' => 'border-indigo-400/30'],
                                    ];
                                    $gradient = $gradients[$index % count($gradients)];
                                @endphp
                                <div class="bg-gradient-to-br {{ $gradient['from'] }} {{ $gradient['to'] }} rounded-xl p-6 text-white shadow-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-xs uppercase tracking-wider {{ $gradient['text'] }} font-medium">Saldo</p>
                                            <p class="text-2xl font-bold mt-1">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</p>
                                        </div>
                                        <svg class="w-8 h-8 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div class="mt-6">
                                        <p class="text-lg font-semibold">{{ $wallet->name }}</p>
                                        <p class="text-xs {{ $gradient['text'] }} mt-1">{{ $wallet->transactions_count }} transaksi</p>
                                    </div>
                                    <div class="mt-4 pt-4 border-t {{ $gradient['border'] }} flex justify-end gap-3">
                                        <a href="{{ route('wallets.edit', $wallet) }}" class="text-sm {{ $gradient['text'] }} hover:text-white font-medium">Edit</a>
                                        <button type="button" @click="showDeleteModal = true; deleteForm = '{{ route('wallets.destroy', $wallet) }}'" class="text-sm {{ $gradient['text'] }} hover:text-white font-medium">Hapus</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Hapus Dompet</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Yakin hapus dompet ini? Semua transaksi terkait juga akan terhapus.</p>
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
