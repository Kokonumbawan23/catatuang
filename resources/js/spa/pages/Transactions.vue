<template>
    <div class="py-10 px-4 sm:px-6 lg:px-8 pb-24 sm:pb-0">
        <div class="max-w-7xl mx-auto space-y-6">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Dashboard Analitik Finansial</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Pantau ringkasan pemasukan, pengeluaran, dan manajemen dompet Anda.</p>
                </div>
                <div class="w-full sm:w-auto flex items-center space-x-3">
                    <select
                        v-model="selectedWalletId"
                        @change="fetchTransactions"
                        class="w-36 rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5 px-2 pr-8"
                    >
                        <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">{{ wallet.name }}</option>
                    </select>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-slate-700 text-center">
                    <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 mb-2">Total Pemasukan</span>
                        <span class="block text-3xl font-extrabold text-green-600 dark:text-green-400">Rp {{ formatNumber(meta.total_income) }}</span>
                    </div>
                    <div class="p-6 hover:bg-gray-50/50 dark:hover:bg-slate-700/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 mb-2">Total Pengeluaran</span>
                        <span class="block text-3xl font-extrabold text-red-600 dark:text-red-400">Rp {{ formatNumber(meta.total_expense) }}</span>
                    </div>
                    <div class="p-6 bg-indigo-50/30 dark:bg-indigo-900/20">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-300 mb-2">Saldo Dompet Saat Ini</span>
                        <span class="block text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">Rp {{ formatNumber(meta.active_wallet_balance) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 space-y-4">
                    <div class="border-b border-gray-100 dark:border-slate-700 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ editingTransaction ? 'Ubah Transaksi' : 'Catat Transaksi Baru' }}</h3>
                        <p class="text-xs text-gray-400 dark:text-slate-400 mt-0.5">{{ editingTransaction ? 'Perbarui data transaksi yang sudah ada.' : 'Input data pemasukan atau pengeluaran secara manual.' }}</p>
                    </div>

                    <div v-if="wallets.length === 0" class="text-center py-6">
                        <p class="text-sm text-gray-500 dark:text-slate-400 mb-4">Anda belum memiliki dompet. Buat dompet terlebih dahulu.</p>
                        <router-link to="/wallets" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700 dark:hover:bg-indigo-700">
                            + Buat Dompet
                        </router-link>
                    </div>
                    <form v-else @submit.prevent="editingTransaction ? updateTransaction() : createTransaction()" class="space-y-4">
                        <div class="flex flex-col justify-between gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Simpan ke Dompet</label>
                            <select
                                v-model="form.wallet_id"
                                required
                                class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 pr-10"
                            >
                                <option :value="null">{{'Pilih Dompet' }}</option>
                                <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">{{ wallet.name }}</option>
                            </select>
                        </div>

                                <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col justify-between gap-1">
                                <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Tipe</label>
                                <select
                                    v-model="form.type"
                                    @change="onTypeChange"
                                    required
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 pr-10"
                                >
                                    <option value="expense">Pengeluaran</option>
                                    <option value="income">Pemasukan</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-between gap-1">
                                <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Nominal (Rp)</label>
                                <input
                                    v-model="form.amountDisplay"
                                    @input="form.amount = parseInt(form.amountDisplay.replace(/[^\d]/g, '') || '0')"
                                    type="text"
                                    inputmode="numeric"
                                    required
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3"
                                    placeholder="Rp 0"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col justify-between gap-1">
                                <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Kategori</label>
                                <select
                                    v-model="form.category_id"
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 pr-10"
                                >
                                    <option :value="null">{{'Pilih Kategori' }}</option>
                                    <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="flex flex-col justify-between gap-1">
                                <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Tanggal</label>
                                <input
                                    v-model="form.transaction_date"
                                    type="date"
                                    required
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col justify-between gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Keterangan / Catatan</label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3"
                                placeholder="Catatan tambahan..."
                            ></textarea>
                        </div>

                        <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-400 dark:border-red-700 rounded-lg">
                            <p class="text-xs text-red-700 dark:text-red-300">{{ formError }}</p>
                        </div>

                        <div class="flex gap-3">
                            <button
                                v-if="editingTransaction"
                                type="button"
                                @click="cancelEdit"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-200 text-sm font-semibold rounded-lg transition-colors"
                            >
                                Batalkan
                            </button>
                            <button
                                type="submit"
                                :disabled="formLoading"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-50"
                            >
                                {{ formLoading ? 'Menyimpan...' : (editingTransaction ? 'Update Transaksi' : 'Simpan Transaksi') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-4">

                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700">
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <div class="flex-1 min-w-[200px]">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3"
                                    placeholder="Cari kata kunci deskripsi..."
                                />
                            </div>
                            <button
                                @click="fetchTransactions"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors"
                            >
                                Cari
                            </button>
                            <button
                                @click="exportTransactions"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors"
                            >
                                Export CSV
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center bg-gray-50/50 dark:bg-slate-700/50">
                            <h3 class="font-bold text-sm text-gray-700 dark:text-slate-300 uppercase tracking-wider">Log Transaksi Berjalan</h3>
                            <div class="w-36 flex items-center gap-2 justify-end">
                                <select
                                    v-model="selectedMonth"
                                    @change="fetchTransactions"
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-xs py-1.5 pr-10"
                                >
                                    <option v-for="m in 12" :key="m" :value="m">{{ monthName(m) }}</option>
                                </select>
                                <select
                                    v-model="selectedYear"
                                    @change="fetchTransactions"
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-xs py-1.5 pr-8"
                                >
                                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="loading" class="p-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Memuat...</p>
                        </div>
                        <div v-else-if="transactions.length === 0" class="p-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada transaksi bulan ini.</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
                                <thead class="bg-gray-50/70 dark:bg-slate-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-slate-400">Tanggal</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-slate-400">Kategori</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-600 dark:text-slate-400">Keterangan</th>
                                        <th class="px-6 py-3 text-right font-semibold text-gray-600 dark:text-slate-400">Nominal</th>
                                        <th class="px-6 py-3 text-center font-semibold text-gray-600 dark:text-slate-400">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 bg-white dark:bg-slate-800">
                                    <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50/60 dark:hover:bg-slate-700/50 transition">
                                        <td class="px-6 py-4 text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ formatDate(transaction.transaction_date) }}</td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-white font-medium whitespace-nowrap">
                                            <span v-if="transaction.category" class="inline-flex items-center gap-1.5">
                                                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: transaction.category.color || '#6366f1' }"></span>
                                                {{ transaction.category.name }}
                                            </span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-slate-400">{{ transaction.description || '-' }}</td>
                                        <td class="px-6 py-4 text-right font-bold whitespace-nowrap" :class="transaction.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ transaction.type === 'income' ? '+' : '-' }} Rp {{ formatNumber(transaction.amount) }}
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap flex justify-around">
                                            <button @click="editTransaction(transaction)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 text-sm font-medium mr-3">Edit</button>
                                            <button @click="confirmDelete(transaction)" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium">Hapus</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-if="pagination && pagination.last_page > 1" class="p-4 border-t border-gray-100 dark:border-slate-700">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
                                    </p>
                                    <div class="flex gap-2">
                                        <button
                                            @click="changePage(pagination.current_page - 1)"
                                            :disabled="pagination.current_page <= 1"
                                            class="px-3 py-1 border border-gray-300 dark:border-slate-600 rounded-md text-xs disabled:opacity-50 hover:bg-gray-50 dark:hover:bg-slate-700"
                                        >
                                            &larr; Sebelumnya
                                        </button>
                                        <button
                                            @click="changePage(pagination.current_page + 1)"
                                            :disabled="pagination.current_page >= pagination.last_page"
                                            class="px-3 py-1 border border-gray-300 dark:border-slate-600 rounded-md text-xs disabled:opacity-50 hover:bg-gray-50 dark:hover:bg-slate-700"
                                        >
                                            Selanjutnya &rarr;
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 dark:bg-slate-900 opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start gap-6">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Hapus Transaksi</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Yakin hapus transaksi ini?</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-slate-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                @click="deleteTransaction"
                                :disabled="formLoading"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-slate-800 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                Hapus
                            </button>
                            <button
                                @click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import axios from 'axios';

const transactions = ref([]);
const wallets = ref([]);
const categories = ref([]);
const meta = ref({ total_income: 0, total_expense: 0, active_wallet_balance: 0 });
const pagination = ref(null);
const loading = ref(true);
const searchQuery = ref('');

const selectedWalletId = ref(null);
const now = new Date();
const selectedMonth = ref(now.getMonth() + 1);
const selectedYear = ref(now.getFullYear());
const years = ref([now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1]);

const showModal = ref(false);
const editingTransaction = ref(null);
const transactionToDelete = ref(null);
const form = reactive({
    wallet_id: null,
    type: 'expense',
    amount: 0,
    amountDisplay: '',
    category_id: null,
    description: '',
    transaction_date: new Date().toISOString().split('T')[0],
});
const formLoading = ref(false);
const formError = ref('');

const filteredCategories = computed(() => {
    if (form.type === 'income') {
        return categories.value.filter(c => c.type === 'income');
    }
    return categories.value.filter(c => c.type === 'expense');
});

const onTypeChange = () => {
    form.category_id = null;
};

const fetchTransactions = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            month: selectedMonth.value,
            year: selectedYear.value,
            search: searchQuery.value || undefined,
        };
        if (selectedWalletId.value) {
            params.wallet_id = selectedWalletId.value;
        }
        const response = await axios.get('/api/transactions', { params });
        transactions.value = response.data.data.data || response.data.data;
        pagination.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
        };
        meta.value = response.data.meta;
        wallets.value = response.data.meta.wallets;
        categories.value = response.data.meta.categories;
        if (wallets.value.length > 0 && !selectedWalletId.value) {
            selectedWalletId.value = response.data.meta.active_wallet?.id || wallets.value[0]?.id;
        }
    } catch (error) {
        console.error('Gagal memuat transaksi:', error);
    } finally {
        loading.value = false;
    }
};

const editTransaction = (transaction) => {
    editingTransaction.value = transaction;
    form.wallet_id = transaction.wallet_id;
    form.type = transaction.type;
    form.amount = transaction.amount;
    form.amountDisplay = transaction.amount ? 'Rp ' + Number(transaction.amount).toLocaleString('id-ID') : '';
    form.category_id = transaction.category_id;
    form.description = transaction.description || '';
    form.transaction_date = transaction.transaction_date ? transaction.transaction_date.split('T')[0] : '';
    formError.value = '';
};

const closeModal = () => {
    showModal.value = false;
    editingTransaction.value = null;
    transactionToDelete.value = null;
    formError.value = '';
};

const cancelEdit = () => {
    editingTransaction.value = null;
    form.wallet_id = wallets.value[0]?.id || null;
    form.type = 'expense';
    form.amount = 0;
    form.amountDisplay = '';
    form.category_id = null;
    form.description = '';
    form.transaction_date = new Date().toISOString().split('T')[0];
    formError.value = '';
};

const confirmDelete = (transaction) => {
    transactionToDelete.value = transaction;
    showModal.value = true;
};

const createTransaction = async () => {
    formLoading.value = true;
    formError.value = '';
    try {
        await axios.post('/api/transactions', form);
        editingTransaction.value = null;
        form.wallet_id = wallets.value[0]?.id || null;
        form.type = 'expense';
        form.amount = 0;
        form.amountDisplay = '';
        form.category_id = null;
        form.description = '';
        form.transaction_date = new Date().toISOString().split('T')[0];
        fetchTransactions();
    } catch (error) {
        formError.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(', ') || 'Gagal menyimpan transaksi.';
    } finally {
        formLoading.value = false;
    }
};

const updateTransaction = async () => {
    formLoading.value = true;
    formError.value = '';
    try {
        await axios.put(`/api/transactions/${editingTransaction.value.id}`, form);
        closeModal();
        fetchTransactions();
    } catch (error) {
        formError.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(', ') || 'Gagal mengupdate transaksi.';
    } finally {
        formLoading.value = false;
    }
};

const deleteTransaction = async () => {
    if (!transactionToDelete.value) return;
    formLoading.value = true;
    try {
        await axios.delete(`/api/transactions/${transactionToDelete.value.id}`);
        closeModal();
        fetchTransactions();
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal menghapus transaksi.');
    } finally {
        formLoading.value = false;
    }
};

const changePage = (page) => {
    if (page >= 1 && pagination.value && page <= pagination.value.last_page) {
        fetchTransactions(page);
    }
};

const exportTransactions = async () => {
    try {
        const params = {
            month: selectedMonth.value,
            year: selectedYear.value,
        };
        if (selectedWalletId.value) {
            params.wallet_id = selectedWalletId.value;
        }
        const response = await axios.get('/api/transactions/export', {
            params,
            responseType: 'blob',
        });
        const blob = new Blob([response.data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Gagal export:', error);
        alert('Gagal export transaksi.');
    }
};

const formatNumber = (value) => Number(value || 0).toLocaleString('id-ID');

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const monthName = (month) => {
    const date = new Date(2000, month - 1, 1);
    return date.toLocaleString('id-ID', { month: 'short' });
};

onMounted(() => {
    fetchTransactions();
});
</script>
