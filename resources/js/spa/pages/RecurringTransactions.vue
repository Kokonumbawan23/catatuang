<template>
    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Transaksi Berulang</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Kelola jadwal transaksi otomatis untuk dompet Anda.</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-slate-400">Aktif</p>
                        <p class="font-bold text-indigo-600 dark:text-indigo-400">{{ meta.total_active_this_month }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-slate-400">Komitmen Bulanan</p>
                        <p class="font-bold text-red-600 dark:text-red-400">Rp {{ formatNumber(meta.monthly_commitment) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 space-y-4">
                    <div class="border-b border-gray-100 dark:border-slate-700 pb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ editingRecurring ? 'Ubah Transaksi Berulang' : 'Transaksi Berulang Baru' }}</h3>
                        <p class="text-xs text-gray-400 dark:text-slate-400 mt-0.5">{{ editingRecurring ? 'Perbarui jadwal transaksi berulang.' : 'Atur transaksi yang akan berjalan otomatis.' }}</p>
                    </div>

                    <div v-if="wallets.length === 0" class="text-center py-6">
                        <p class="text-sm text-gray-500 dark:text-slate-400 mb-4">Anda belum memiliki dompet.</p>
                        <router-link to="/wallets" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                            + Buat Dompet
                        </router-link>
                    </div>
                    <form v-else @submit.prevent="editingRecurring ? updateRecurring() : createRecurring()" class="space-y-4">
                        <div class="flex flex-col justify-between gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Judul</label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3"
                                placeholder="Contoh: Bayar WiFi"
                            />
                        </div>

                        <div class="flex flex-col justify-between gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Dompet</label>
                            <select
                                v-model="form.wallet_id"
                                required
                                class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 pr-10"
                            >
                                <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">{{ wallet.name }}</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col justify-between gap-1">
                                <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Tipe</label>
                                <select
                                    v-model="form.type"
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

                        <div class="flex flex-col justify-between gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Kategori</label>
                            <select
                                v-model="form.category_id"
                                class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 pr-10"
                            >
                                <option :value="null">-</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>

                        <div class="flex flex-col justify-between gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Frekuensi</label>
                            <select
                                v-model="form.frequency"
                                required
                                class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 pr-10"
                            >
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                        </div>

                        <div class="flex flex-col justify-between gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Konfigurasi Jadwal</label>
                            <div class="rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 p-3 space-y-2">
                                <div v-if="form.frequency === 'daily'">
                                    <label class="text-xs text-gray-500 dark:text-slate-400">Interval (hari)</label>
                                    <input
                                        v-model.number="form.schedule_config.interval_days"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 mt-1"
                                        placeholder="1"
                                    />
                                </div>
                                <div v-else-if="form.frequency === 'weekly'">
                                    <label class="text-xs text-gray-500 dark:text-slate-400 mb-1 block">Hari</label>
                                    <div class="flex flex-wrap gap-2">
                                        <label v-for="day in weekDays" :key="day.value" class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :value="day.value"
                                                v-model="form.schedule_config.day_of_week"
                                                class="rounded border-gray-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <span class="text-xs text-gray-700 dark:text-slate-300">{{ day.label }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div v-else-if="form.frequency === 'monthly'">
                                    <div class="space-y-2">
                                        <div>
                                            <label class="text-xs text-gray-500 dark:text-slate-400">Tanggal</label>
                                            <input
                                                v-model.number="form.schedule_config.day_of_month"
                                                type="number"
                                                min="1"
                                                max="31"
                                                required
                                                class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 mt-1"
                                                placeholder="1"
                                            />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500 dark:text-slate-400">Interval (bulan)</label>
                                            <input
                                                v-model.number="form.schedule_config.interval_months"
                                                type="number"
                                                min="1"
                                                class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 mt-1"
                                                placeholder="1"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div v-else-if="form.frequency === 'yearly'">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-xs text-gray-500 dark:text-slate-400">Tanggal</label>
                                            <input
                                                v-model.number="form.schedule_config.day_of_month"
                                                type="number"
                                                min="1"
                                                max="31"
                                                required
                                                class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 mt-1"
                                                placeholder="1"
                                            />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500 dark:text-slate-400">Bulan</label>
                                            <select
                                                v-model.number="form.schedule_config.month_of_year"
                                                required
                                                class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 mt-1 pr-8"
                                            >
                                                <option v-for="m in 12" :key="m" :value="m">{{ monthName(m) }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col justify-between gap-1">
                                <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Mulai</label>
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    required
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3"
                                />
                            </div>
                            <div class="flex flex-col justify-between gap-1">
                                <label class="text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Selesai</label>
                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                id="is_active"
                                class="rounded border-gray-300 dark:border-slate-500 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label for="is_active" class="text-sm text-gray-700 dark:text-slate-300 cursor-pointer">Aktif sekarang</label>
                        </div>

                        <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-400 dark:border-red-700 rounded-lg">
                            <p class="text-xs text-red-700 dark:text-red-300">{{ formError }}</p>
                        </div>

                        <div class="flex gap-3">
                            <button
                                v-if="editingRecurring"
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
                                {{ formLoading ? 'Menyimpan...' : (editingRecurring ? 'Update' : 'Simpan') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-700/50">
                            <h3 class="font-bold text-sm text-gray-700 dark:text-slate-300 uppercase tracking-wider">Daftar Transaksi Berulang</h3>
                        </div>

                        <div v-if="loading" class="p-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Memuat...</p>
                        </div>
                        <div v-else-if="recurrings.length === 0" class="p-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada transaksi berulang.</p>
                        </div>
                        <div v-else class="divide-y divide-gray-100 dark:divide-slate-700">
                            <div
                                v-for="item in recurrings"
                                :key="item.id"
                                class="p-5 hover:bg-gray-50/60 dark:hover:bg-slate-700/50 transition"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.title }}</span>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                :class="item.is_active ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'"
                                            >
                                                {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-slate-400">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: item.category?.color || '#6366f1' }"></span>
                                                {{ item.category?.name || '-' }}
                                            </span>
                                            <span>{{ frequencyLabel(item.frequency) }}</span>
                                            <span>{{ formatDate(item.start_date) }}</span>
                                            <span v-if="item.end_date">- {{ formatDate(item.end_date) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold" :class="item.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            {{ item.type === 'income' ? '+' : '-' }} Rp {{ formatNumber(item.amount) }}
                                        </p>
                                        <div class="flex items-center justify-end gap-2 mt-2">
                                            <button
                                                @click="toggleActive(item)"
                                                class="text-xs px-2 py-1 rounded border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-600 dark:text-slate-400 transition"
                                                :class="item.is_active ? 'text-green-600 dark:text-green-400 border-green-200 dark:border-green-800' : 'text-gray-500'"
                                            >
                                                {{ item.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                            <button
                                                @click="editRecurring(item)"
                                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="confirmDelete(item)"
                                                class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Hapus Transaksi Berulang</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Yakin hapus transaksi berulang "{{ recurringToDelete?.title }}"?</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-slate-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                @click="deleteRecurring"
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
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const recurrings = ref([]);
const wallets = ref([]);
const categories = ref([]);
const pagination = ref(null);
const loading = ref(true);

const meta = ref({ total_active_this_month: 0, monthly_commitment: 0 });

const showModal = ref(false);
const editingRecurring = ref(null);
const recurringToDelete = ref(null);
const formLoading = ref(false);
const formError = ref('');

const weekDays = [
    { value: 0, label: 'Min' },
    { value: 1, label: 'Sen' },
    { value: 2, label: 'Sel' },
    { value: 3, label: 'Rab' },
    { value: 4, label: 'Kam' },
    { value: 5, label: 'Jum' },
    { value: 6, label: 'Sab' },
];

const defaultScheduleConfig = {
    daily: { interval_days: 1 },
    weekly: { day_of_week: [] },
    monthly: { day_of_month: 1, interval_months: 1 },
    yearly: { day_of_month: 1, month_of_year: 1 },
};

const form = reactive({
    wallet_id: null,
    title: '',
    amount: 0,
    amountDisplay: '',
    type: 'expense',
    category_id: null,
    frequency: 'monthly',
    schedule_config: { ...defaultScheduleConfig.monthly },
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    is_active: true,
});

const fetchRecurrings = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/recurring-transactions', { params: { page } });
        recurrings.value = response.data.data.data || response.data.data;
        pagination.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
        };
        meta.value = response.data.meta;
        wallets.value = response.data.meta.wallets;
        categories.value = response.data.meta.categories;
        if (wallets.value.length > 0 && !form.wallet_id) {
            form.wallet_id = wallets.value[0].id;
        }
    } catch (error) {
        console.error('Gagal memuat:', error);
    } finally {
        loading.value = false;
    }
};

const editRecurring = (item) => {
    editingRecurring.value = item;
    form.wallet_id = item.wallet_id;
    form.title = item.title;
    form.amount = item.amount;
    form.amountDisplay = item.amount ? 'Rp ' + Number(item.amount).toLocaleString('id-ID') : '';
    form.type = item.type;
    form.category_id = item.category_id;
    form.frequency = item.frequency;
    form.schedule_config = { ...item.schedule_config };
    form.start_date = item.start_date ? item.start_date.split('T')[0] : '';
    form.end_date = item.end_date ? item.end_date.split('T')[0] : '';
    form.is_active = item.is_active;
    formError.value = '';
};

const cancelEdit = () => {
    editingRecurring.value = null;
    form.wallet_id = wallets.value[0]?.id || null;
    form.title = '';
    form.amount = 0;
    form.amountDisplay = '';
    form.type = 'expense';
    form.category_id = null;
    form.frequency = 'monthly';
    form.schedule_config = { ...defaultScheduleConfig.monthly };
    form.start_date = new Date().toISOString().split('T')[0];
    form.end_date = '';
    form.is_active = true;
    formError.value = '';
};

const closeModal = () => {
    showModal.value = false;
    recurringToDelete.value = null;
};

const confirmDelete = (item) => {
    recurringToDelete.value = item;
    showModal.value = true;
};

const createRecurring = async () => {
    formLoading.value = true;
    formError.value = '';
    try {
        await axios.post('/api/recurring-transactions', {
            ...form,
            schedule_config: { ...form.schedule_config },
        });
        cancelEdit();
        fetchRecurrings();
    } catch (error) {
        formError.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(', ') || 'Gagal menyimpan.';
    } finally {
        formLoading.value = false;
    }
};

const updateRecurring = async () => {
    formLoading.value = true;
    formError.value = '';
    try {
        await axios.put(`/api/recurring-transactions/${editingRecurring.value.id}`, {
            ...form,
            schedule_config: { ...form.schedule_config },
        });
        cancelEdit();
        fetchRecurrings();
    } catch (error) {
        formError.value = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(', ') || 'Gagal memperbarui.';
    } finally {
        formLoading.value = false;
    }
};

const deleteRecurring = async () => {
    if (!recurringToDelete.value) return;
    formLoading.value = true;
    try {
        await axios.delete(`/api/recurring-transactions/${recurringToDelete.value.id}`);
        closeModal();
        fetchRecurrings();
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal menghapus.');
    } finally {
        formLoading.value = false;
    }
};

const toggleActive = async (item) => {
    try {
        await axios.patch(`/api/recurring-transactions/${item.id}/toggle`);
        fetchRecurrings();
    } catch (error) {
        console.error('Gagal toggle:', error);
    }
};

const changePage = (page) => {
    if (page >= 1 && pagination.value && page <= pagination.value.last_page) {
        fetchRecurrings(page);
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

const frequencyLabel = (freq) => {
    const labels = { daily: 'Harian', weekly: 'Mingguan', monthly: 'Bulanan', yearly: 'Tahunan' };
    return labels[freq] || freq;
};

onMounted(() => {
    fetchRecurrings();
});
</script>
