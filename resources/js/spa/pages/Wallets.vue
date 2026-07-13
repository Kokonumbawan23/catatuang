<template>
    <div class="py-10 bg-gray-100 dark:bg-slate-900 min-h-screen pb-24 sm:pb-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Dompet Saya</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Kelola semua dompet dan saldo Anda.</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm"
                >
                    + Tambah Dompet
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-white">
                    <div v-if="loading" class="text-center py-12">
                        <p class="text-sm text-gray-500 dark:text-slate-400">Memuat...</p>
                    </div>

                    <div v-else-if="wallets.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada dompet</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Mulai dengan membuat dompet pertama Anda.</p>
                        <div class="mt-6">
                            <button
                                @click="openCreateModal"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                            >
                                + Buat Dompet
                            </button>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            v-for="(wallet, index) in wallets"
                            :key="wallet.id"
                            :class="gradientClasses[index % gradientClasses.length]"
                            class="rounded-xl p-6 text-white shadow-lg"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-xs uppercase tracking-wider opacity-75 font-medium">Saldo</p>
                                    <p class="text-2xl font-bold mt-1">Rp {{ formatNumber(wallet.balance) }}</p>
                                </div>
                                <svg class="w-8 h-8 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div class="mt-6">
                                <p class="text-lg font-semibold">{{ wallet.name }}</p>
                                <p class="text-xs opacity-75 mt-1">{{ wallet.transactions_count || 0 }} transaksi</p>
                                <p v-if="wallet.balance_limit && wallet.balance_limit > 0" class="text-xs opacity-60 mt-1">
                                    Batas Bawah: Rp {{ formatNumber(wallet.balance_limit) }}
                                </p>
                            </div>
                            <div class="mt-4 pt-4 border-t border-white/20 flex justify-end gap-3">
                                <button @click="editWallet(wallet)" class="text-sm opacity-75 hover:opacity-100 font-medium">Edit</button>
                                <button @click="confirmDelete(wallet)" class="text-sm opacity-75 hover:opacity-100 font-medium">Hapus</button>
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
                        <div class="absolute inset-0 bg-gray-500 dark:bg-black opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start gap-6">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Hapus Dompet</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Yakin hapus dompet ini? Semua transaksi terkait juga akan terhapus.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-slate-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                @click="deleteWallet"
                                :disabled="formLoading"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                Hapus
                            </button>
                            <button
                                @click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-700 text-base font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="modal">
            <div v-if="showFormModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 dark:bg-black opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start gap-6">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                        {{ editingWallet ? 'Edit Dompet' : 'Tambah Dompet' }}
                                    </h3>
                                    <form @submit.prevent="editingWallet ? updateWallet() : createWallet()" class="mt-4 space-y-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider mb-1">Nama Dompet</label>
                                            <input
                                                v-model="walletForm.name"
                                                type="text"
                                                required
                                                class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3"
                                                placeholder="Contoh: Dompet Utama"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider mb-1">
                                                {{ editingWallet ? 'Saldo Saat Ini (Rp)' : 'Saldo Awal (Rp)' }}
                                            </label>
                                            <input
                                                v-model.number="walletForm.balance"
                                                type="number"
                                                min="0"
                                                required
                                                class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3"
                                                placeholder="0"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider mb-1">
                                                Batas Bawah Saldo (Rp) <span class="text-gray-400 font-normal normal-case">- opsional</span>
                                            </label>
                                            <input
                                                v-model.number="walletForm.balance_limit"
                                                type="number"
                                                min="0"
                                                class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3"
                                                placeholder="Contoh: 100000"
                                            />
                                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Notifikasi akan muncul saat saldo hampir mencapai atau di bawah batas ini.</p>
                                        </div>
                                        <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-400 dark:border-red-700 rounded-lg">
                                            <p class="text-xs text-red-700 dark:text-red-300">{{ formError }}</p>
                                        </div>
                                        <div class="flex justify-end gap-3 pt-2">
                                            <button
                                                type="button"
                                                @click="closeModal"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600"
                                            >
                                                Batal
                                            </button>
                                            <button
                                                type="submit"
                                                :disabled="formLoading"
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
                                            >
                                                {{ formLoading ? 'Menyimpan...' : (editingWallet ? 'Update' : 'Simpan') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const wallets = ref([]);
const loading = ref(true);
const showModal = ref(false);
const showFormModal = ref(false);
const editingWallet = ref(null);
const walletToDelete = ref(null);
const walletForm = ref({ name: '', balance: 0, balance_limit: null });
const formLoading = ref(false);
const formError = ref('');

const gradientClasses = [
    'bg-gradient-to-br from-emerald-500 to-teal-600 text-emerald-100 border-emerald-400/30',
    'bg-gradient-to-br from-blue-500 to-cyan-600 text-blue-100 border-blue-400/30',
    'bg-gradient-to-br from-purple-500 to-pink-600 text-purple-100 border-purple-400/30',
    'bg-gradient-to-br from-orange-500 to-red-600 text-orange-100 border-orange-400/30',
    'bg-gradient-to-br from-indigo-500 to-purple-600 text-indigo-100 border-indigo-400/30',
];

const fetchWallets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/wallets');
        wallets.value = response.data.data;
    } catch (error) {
        console.error('Gagal memuat dompet:', error);
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    editingWallet.value = null;
    walletForm.value = { name: '', balance: 0, balance_limit: null };
    formError.value = '';
    showFormModal.value = true;
};

const editWallet = (wallet) => {
    editingWallet.value = wallet;
    walletForm.value = { name: wallet.name, balance: wallet.balance, balance_limit: wallet.balance_limit || null };
    formError.value = '';
    showFormModal.value = true;
};

const confirmDelete = (wallet) => {
    walletToDelete.value = wallet;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    showFormModal.value = false;
    editingWallet.value = null;
    walletToDelete.value = null;
    walletForm.value = { name: '', balance: 0, balance_limit: null };
    formError.value = '';
};

const createWallet = async () => {
    formLoading.value = true;
    formError.value = '';
    try {
        await axios.post('/api/wallets', walletForm.value);
        closeModal();
        fetchWallets();
    } catch (error) {
        formError.value = error.response?.data?.message || 'Gagal membuat dompet.';
    } finally {
        formLoading.value = false;
    }
};

const updateWallet = async () => {
    formLoading.value = true;
    formError.value = '';
    try {
        await axios.put(`/api/wallets/${editingWallet.value.id}`, walletForm.value);
        closeModal();
        fetchWallets();
    } catch (error) {
        formError.value = error.response?.data?.message || 'Gagal mengupdate dompet.';
    } finally {
        formLoading.value = false;
    }
};

const deleteWallet = async () => {
    if (!walletToDelete.value) return;
    formLoading.value = true;
    try {
        await axios.delete(`/api/wallets/${walletToDelete.value.id}`);
        closeModal();
        fetchWallets();
    } catch (error) {
        alert(error.response?.data?.message || 'Gagal menghapus dompet.');
    } finally {
        formLoading.value = false;
    }
};

const formatNumber = (value) => {
    return Number(value || 0).toLocaleString('id-ID');
};

onMounted(() => {
    fetchWallets();
});
</script>
