<x-app-layout>
    <div class="py-10 bg-gray-100 dark:bg-slate-900 min-h-screen pb-24 sm:pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Dashboard</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Ringkasan aktivitas finansial Anda.</p>
                </div>
                <form method="GET" action="{{ route('dashboard') }}" class="flex sm:flex-row gap-2 justify-start">
                    <x-select name="month" :options="collect(range(1, 12))->map(fn($m) => ['value' => $m, 'label' => date('F', mktime(0, 0, 0, $m, 1))])" value="{{ $month }}" class="w-36 dark:bg-slate-700 dark:text-white dark:border-slate-600" />
                    <x-select name="year" :options="collect(range(now()->year - 2, now()->year + 1))->map(fn($y) => ['value' => $y, 'label' => $y])" value="{{ $year }}" class="w-28 dark:bg-slate-700 dark:text-white dark:border-slate-600" />
                    <button type="submit" class="px-6 py-1.5 bg-gray-800 dark:bg-indigo-600 text-white text-sm rounded-lg hover:bg-gray-700 dark:hover:bg-indigo-700 transition-colors">
                        Filter
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Total Pemasukan</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Jumlah Transaksi</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $countMonth }}
                    </p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-6">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Rata-rata Transaksi</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                        Rp {{ number_format($avgMonth, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribusi Pengeluaran per Kategori</h3>
                        @if ($categoryData->isEmpty())
                            <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada data pengeluaran.</p>
                        @else
                            <div class="relative h-64">
                                <canvas id="expenseChart"></canvas>
                            </div>
                            <div class="mt-6 grid grid-cols-2 gap-2">
                                @foreach ($categoryData as $cat)
                                    <div class="flex items-center gap-1 text-sm">
                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                        <span class="text-gray-600 dark:text-slate-300">{{ $cat['name'] }}</span>
                                        <span class="ml-auto font-medium text-gray-900 dark:text-white">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transaksi Terbaru</h3>
                            <a href="{{ route('transactions.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">
                                Lihat Semua →
                            </a>
                        </div>
                        @if ($recentTransactions->isEmpty())
                            <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada transaksi bulan ini.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($recentTransactions as $transaction)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-700/50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm"
                                                 style="background-color: {{ $transaction->category->color ?? ($transaction->type === 'income' ? '#10b981' : '#ef4444') }}">
                                                {{ $transaction->type === 'income' ? '+' : '-' }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $transaction->description ?? ($transaction->category->name ?? 'Transaksi') }}</p>
                                                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $transaction->transaction_date->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                        <p class="font-semibold {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </p>
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
