<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengeluaran') }}
            </h2>
            <a href="{{ route('expenses.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 touch-manipulation">
                + Tambah
            </a>
        </div>
    </x-slot>

    <div class="py-6 px-2 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 sm:p-6 text-gray-900">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-2">
                        <span class="text-sm text-gray-600">Total Bulan Ini</span>
                        <span class="text-xl sm:text-2xl font-bold text-indigo-600">Rp {{ number_format($totalMonth, 0, ',', '.') }}</span>
                    </div>
                    <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap gap-2 mt-4">
                        <select name="month" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700 touch-manipulation">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900">
                    @if ($expenses->isEmpty())
                        <p class="text-center text-gray-500 py-8">Belum ada pengeluaran bulan ini.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($expenses as $expense)
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-lg flex-shrink-0"
                                             style="background-color: {{ $expense->category->color ?? '#6366f1' }}">
                                            {{ strtoupper(substr($expense->category->name ?? 'X', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 truncate">{{ $expense->category->name ?? 'Tanpa Kategori' }}</p>
                                            <p class="text-xs sm:text-sm text-gray-500 truncate">
                                                {{ $expense->spent_at->format('d M Y') }}
                                                @if ($expense->description)
                                                    <span class="mx-1">•</span> {{ Str::limit($expense->description, 20) }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end gap-2 pl-13 sm:pl-0">
                                        <p class="font-semibold text-gray-900 whitespace-nowrap">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                        <div class="flex gap-2 sm:gap-3">
                                            <a href="{{ route('expenses.edit', $expense) }}"
                                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium px-2 py-1 touch-manipulation">Edit</a>
                                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Yakin hapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium px-2 py-1 touch-manipulation">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $expenses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
