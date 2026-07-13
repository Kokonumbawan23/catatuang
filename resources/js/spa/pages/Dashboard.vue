<template>
    <div class="py-10 px-4 sm:px-6 lg:px-8 pb-24 sm:pb-0">
        <div class="max-w-7xl mx-auto space-y-6">

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Dashboard</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Ringkasan finansial Anda hari ini.</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">Dompet:</label>
                    <select
                        v-model="selectedWalletId"
                        @change="fetchDashboard"
                        class="w-48 rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 px-2 pr-10"
                    >
                        <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">
                            {{ wallet.name }}
                        </option>
                    </select>
                </div>
            </div>

            <Transition name="slide-down">
                <div
                    v-if="showBalanceAlert && activeWallet && activeWallet.balance_limit"
                    class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-start gap-3"
                >
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-200">Peringatan Buffer Habis!</p>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                            Buffer saldo Anda sudah habis. Sisa saldo (Rp {{ formatNumber(summary.balance) }}) berada di bawah batas minimum (Rp {{ formatNumber(activeWallet.balance_limit) }}).
                        </p>
                    </div>
                    <button @click="dismissAlert" class="text-red-500 hover:text-red-700 dark:hover:text-red-300 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </Transition>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-slate-700 text-center">
                    <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 mb-2">Total Pemasukan</span>
                        <span class="block text-3xl font-extrabold text-green-600 dark:text-green-400">Rp {{ formatNumber(summary.total_income) }}</span>
                    </div>
                    <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 mb-2">Total Pengeluaran</span>
                        <span class="block text-3xl font-extrabold text-red-600 dark:text-red-400">Rp {{ formatNumber(summary.total_expense) }}</span>
                    </div>
                    <div class="p-6 bg-indigo-50/30 dark:bg-indigo-900/20">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-300 mb-2">Saldo Dompet Saat Ini</span>
                        <span class="block text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ formatNumber(summary.balance) }}</span>
                        <div v-if="activeWallet && activeWallet.balance_limit && activeWallet.balance_limit > 0" class="mt-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-indigo-700 dark:text-indigo-300">Sisa Buffer</span>
                                <span class="text-xs font-medium text-indigo-700 dark:text-indigo-300">Rp {{ formatNumber(Math.max(0, remainingBuffer || 0)) }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-indigo-200 dark:bg-indigo-900 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="progressBarColor"
                                    :style="{ width: progressBarWidth }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center bg-gray-50/50 dark:bg-slate-700/50">
                        <h3 class="font-bold text-sm text-gray-700 dark:text-slate-300 uppercase tracking-wider">Transaksi Terbaru</h3>
                        <router-link to="/transactions" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                            Lihat semua &rarr;
                        </router-link>
                    </div>

                    <div v-if="loading" class="p-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-slate-400">Memuat...</p>
                    </div>
                    <div v-else-if="recentTransactions.length === 0" class="p-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada transaksi.</p>
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                            <thead class="bg-gray-50/70 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-slate-400">Tanggal</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-slate-400">Kategori</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-slate-400">Keterangan</th>
                                    <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-slate-400">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700 bg-white dark:bg-slate-800">
                                <tr v-for="transaction in recentTransactions" :key="transaction.id" class="hover:bg-gray-50/60 dark:hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4 text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ formatDate(transaction.transaction_date) }}</td>
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium whitespace-nowrap">
                                        <span v-if="transaction.category" class="inline-flex items-center gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: transaction.category.color || '#6366f1' }"></span>
                                            {{ transaction.category.name }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-slate-400">{{ transaction.description || '-' }}</td>
                                    <td class="px-6 py-4 text-right font-bold whitespace-nowrap" :class="transaction.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                        {{ transaction.type === 'income' ? '+' : '-' }} Rp {{ formatNumber(transaction.amount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-700/50">
                        <h3 class="font-bold text-sm text-gray-700 dark:text-slate-300 uppercase tracking-wider">Pengeluaran per Kategori</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="loading" class="p-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Memuat...</p>
                        </div>
                        <div v-else-if="categoryData.length === 0" class="p-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada data pengeluaran.</p>
                        </div>
                        <div v-else class="w-full flex flex-col gap-6 items-center">
                            <canvas ref="categoryChartCanvas" class="max-h-40"></canvas>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div v-for="item in categoryData" :key="item.name" class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: item.color }"></span>
                                    <span class="text-gray-600 dark:text-slate-400">{{ item.name }}</span>
                                    <span class="ml-auto font-semibold text-gray-700 dark:text-slate-200">Rp {{ formatNumber(item.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import axios from 'axios';
import { Chart, DoughnutController, ArcElement, Tooltip, Legend } from 'chart.js';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const wallets = ref([]);
const selectedWalletId = ref(null);
const recentTransactions = ref([]);
const summary = ref({ total_income: 0, total_expense: 0, balance: 0 });
const categoryData = ref([]);
const loading = ref(false);
const categoryChartCanvas = ref(null);
let categoryChart = null;
const activeWallet = ref(null);
const showBalanceAlert = ref(false);

const fetchDashboard = async () => {
    loading.value = true;
    try {
        const params = {};
        if (selectedWalletId.value) {
            params.wallet_id = selectedWalletId.value;
        }
        const response = await axios.get('/api/dashboard', { params });
        wallets.value = response.data.data.wallets;
        recentTransactions.value = response.data.data.recent_transactions;
        summary.value = response.data.data.summary;
        categoryData.value = response.data.data.category_data || [];
        if (response.data.data.active_wallet) {
            selectedWalletId.value = response.data.data.active_wallet.id;
            activeWallet.value = response.data.data.active_wallet;
            if (activeWallet.value && activeWallet.value.balance_limit && activeWallet.value.balance_limit > 0) {
                showBalanceAlert.value = summary.value.balance <= activeWallet.value.balance_limit;
            } else {
                showBalanceAlert.value = false;
            }
        }
        await nextTick();
        renderCategoryChart();
    } catch (error) {
        console.error('Gagal memuat dashboard:', error);
    } finally {
        loading.value = false;
    }
};

const renderCategoryChart = () => {
    if (!categoryChartCanvas.value || categoryData.value.length === 0) return;
    if (categoryChart) {
        categoryChart.destroy();
    }
    categoryChart = new Chart(categoryChartCanvas.value, {
        type: 'doughnut',
        data: {
            labels: categoryData.value.map(c => c.name),
            datasets: [{
                data: categoryData.value.map(c => c.total),
                backgroundColor: categoryData.value.map(c => c.color),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.2,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const value = context.raw;
                            return ' Rp ' + Number(value).toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
};

const formatNumber = (value) => {
    return Number(value || 0).toLocaleString('id-ID');
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

watch(selectedWalletId, () => {
    fetchDashboard();
});

const remainingBuffer = computed(() => {
    if (!activeWallet.value || !activeWallet.value.balance_limit || activeWallet.value.balance_limit <= 0) {
        return null;
    }
    return summary.value.balance - activeWallet.value.balance_limit;
});

const bufferPercentage = computed(() => {
    if (remainingBuffer.value === null || activeWallet.value.balance_limit <= 0) {
        return 0;
    }
    const percentage = (remainingBuffer.value / activeWallet.value.balance_limit) * 100;
    return Math.max(0, Math.min(percentage, 100));
});

const progressBarWidth = computed(() => {
    return `${bufferPercentage.value}%`;
});

const progressBarColor = computed(() => {
    if (remainingBuffer.value === null) {
        return 'bg-indigo-500';
    }
    if (remainingBuffer.value > 0) {
        return 'bg-green-500';
    }
    if (remainingBuffer.value >= activeWallet.value.balance_limit * 0.5) {
        return 'bg-yellow-500';
    }
    if (remainingBuffer.value >= 0) {
        return 'bg-yellow-500';
    }
    return 'bg-red-500';
});

const dismissAlert = () => {
    showBalanceAlert.value = false;
};

onMounted(() => {
    fetchDashboard();
});
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
