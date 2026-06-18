<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <form method="GET" action="{{ route('dashboard') }}" class="flex gap-2">
                <select name="month" class="rounded-md border-gray-300 shadow-sm text-sm">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="rounded-md border-gray-300 shadow-sm text-sm">
                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="px-3 py-1 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                    Filter
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-1">Total Bulan Ini</p>
                    <p class="text-2xl font-bold text-indigo-600">
                        Rp {{ number_format($totalMonth, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-1">Jumlah Transaksi</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $countMonth }}
                    </p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 mb-1">Rata-rata Pengeluaran</p>
                    <p class="text-2xl font-bold text-orange-600">
                        Rp {{ number_format($avgMonth, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Distribusi Pengeluaran per Kategori</h3>
                    @if ($categoryData->isEmpty())
                        <p class="text-center text-gray-500 py-8">Belum ada data pengeluaran.</p>
                    @else
                        <div class="relative h-64">
                            <canvas id="expenseChart"></canvas>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            @foreach ($categoryData as $cat)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                    <span class="text-gray-600">{{ $cat['name'] }}</span>
                                    <span class="ml-auto font-medium">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Pengeluaran Terbaru</h3>
                            <a href="{{ route('expenses.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                Lihat Semua →
                            </a>
                        </div>
                        @if ($recentExpenses->isEmpty())
                            <p class="text-center text-gray-500 py-8">Belum ada pengeluaran bulan ini.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($recentExpenses as $expense)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm"
                                                 style="background-color: {{ $expense->category->color ?? '#6366f1' }}">
                                                {{ strtoupper(substr($expense->category->name ?? 'X', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $expense->category->name ?? 'Tanpa Kategori' }}</p>
                                                <p class="text-xs text-gray-500">{{ $expense->spent_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                        <p class="font-semibold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryData = @json($categoryData);
            if (categoryData.length > 0) {
                const ctx = document.getElementById('expenseChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryData.map(c => c.name),
                        datasets: [{
                            data: categoryData.map(c => c.total),
                            backgroundColor: categoryData.map(c => c.color),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
