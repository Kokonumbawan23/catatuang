<x-app-layout>
    <script>
        document.addEventListener('alpine:init', () => {
            console.log('Initializing Alpine.js for Recurring Transactions');
            console.log('CSRF Token:', '{{ csrf_token() }}');
            console.log('Base URL:', '{{ url('recurring-transactions') }}');
            Alpine.data('recurringApp', () => ({
                showModal: false,
                modalMode: 'create',
                editingId: null,
                baseUrl: '{{ url('recurring-transactions') }}',
                csrfToken: '{{ csrf_token() }}',
                form: {
                    wallet_id: '',
                    title: '',
                    type: 'expense',
                    amount: '',
                    category_id: '',
                    frequency: 'monthly',
                    schedule_config: { day_of_month: 1 },
                    start_date: new Date().toISOString().split('T')[0],
                    end_date: '',
                    is_active: true,
                    requires_confirmation: false
                },
                errors: {},
                toasts: [],
                weekdays: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],

                openCreate() {

                    this.modalMode = 'create';
                    this.editingId = null;
                    this.resetForm();
                    this.showModal = true;
                },

                openEdit(r) {
                    this.modalMode = 'edit';
                    this.editingId = r.id;
                    this.form = {
                        wallet_id: r.wallet_id,
                        title: r.title,
                        type: r.type,
                        amount: r.amount.toString(),
                        category_id: r.category_id || '',
                        frequency: r.frequency,
                        schedule_config: typeof r.schedule_config === 'string' ? JSON.parse(r.schedule_config) : (r.schedule_config || {}),
                        start_date: r.start_date,
                        end_date: r.end_date || '',
                        is_active: r.is_active,
                        requires_confirmation: r.requires_confirmation || false
                    };
                    this.errors = {};
                    this.showModal = true;
                },

                resetForm() {
                    this.form = {
                        wallet_id: '',
                        title: '',
                        type: 'expense',
                        amount: '',
                        category_id: '',
                        frequency: 'monthly',
                        schedule_config: { day_of_month: 1 },
                        start_date: new Date().toISOString().split('T')[0],
                        end_date: '',
                        is_active: true,
                        requires_confirmation: false
                    };
                    this.errors = {};
                },

                showToast(message, type = 'error') {
                    const id = Date.now();
                    this.toasts.push({ id, message, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 4000);
                },

                closeModal() {
                    this.showModal = false;
                },

                async saveRecurring() {
                    console.log('Saving recurring transaction:', this.form);
                    this.errors = {};
                     const method = this.modalMode === 'create' ? 'POST' : 'PUT';
                     const url = this.modalMode === 'create'
                         ? this.baseUrl
                         : this.baseUrl + '/' + this.editingId;

                     try {
                         const response = await fetch(url, {
                             method: method,
                             headers: {
                                 'X-CSRF-TOKEN': this.csrfToken,
                                 'Content-Type': 'application/json',
                                 'Accept': 'application/json',
                             },
                             body: JSON.stringify({
                                 ...this.form,
                                 amount: this.parseCurrency(this.form.amount),
                             })
                         });

                         if (response.redirected) {
                             window.location.href = response.url;
                             return;
                         }

                         const data = await response.json();

                         if (!response.ok) {
                             if (data.errors) {
                                 this.errors = data.errors;
                                 const firstError = Object.values(data.errors).flat()[0];
                                 this.showToast(firstError || 'Validasi gagal.', 'error');
                             } else {
                                 this.showToast(data.message || 'Terjadi kesalahan.', 'error');
                             }
                             return;
                         }

                         this.showToast(data.message || 'Berhasil disimpan.', 'success');
                         this.closeModal();
                         window.location.reload();
                     } catch (error) {
                         console.error('Error:', error);
                         this.showToast('Terjadi kesalahan koneksi.', 'error');
                    }
                },

                async toggleActive(id, currentState) {
                    try {
                        const response = await fetch(this.baseUrl + '/' + id, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ is_active: !currentState })
                        });

                        if (response.ok) {
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },

                confirmDelete(id) {
                    if (confirm('Yakin hapus transaksi berulang ini?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = this.baseUrl + '/' + id;
                        form.innerHTML = '<input type="hidden" name="_token" value="' + this.csrfToken + '"><input type="hidden" name="_method" value="DELETE">';
                        document.body.appendChild(form);
                        form.submit();
                    }
                },

                formatCurrency(value) {
                    const num = value.replace(/[^0-9]/g, '');
                    return 'Rp ' + num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },

                parseCurrency(str) {
                    return parseInt(str.replace(/[^0-9]/g, '')) || 0;
                },

                getFrequencyLabel(freq) {
                    const labels = {
                        'daily': 'Harian',
                        'weekly': 'Mingguan',
                        'monthly': 'Bulanan',
                        'yearly': 'Tahunan'
                    };
                    return labels[freq] || freq;
                },

                getWeekdayLabels(days) {
                    if (!days || days.length === 0) return 'Tidak ada';
                    return days.map(d => this.weekdays[d - 1]).join(', ');
                }
            }));
        });
    </script>

    <div class="py-10 bg-gray-100 dark:bg-slate-900 min-h-screen pb-24 sm:pb-10"
         x-data="recurringApp()">

        <div class="fixed top-4 right-4 z-50 space-y-2 w-80 max-w-full"
             x-show="toasts.length > 0"
             x-transition>
            <template x-for="toast in toasts" :key="toast.id">
                <div class="flex items-start gap-3 p-4 rounded-xl shadow-lg border"
                     :class="{
                         'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-700 text-red-800 dark:text-red-200': toast.type === 'error',
                         'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-700 text-green-800 dark:text-green-200': toast.type === 'success'
                     }">
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <p class="text-sm font-medium" x-text="toast.message"></p>
                    <button @click="toasts = toasts.filter(t => t.id !== toast.id)"
                            class="ml-auto flex-shrink-0 opacity-60 hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Transaksi Berulang</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Kelola transaksi otomatis yang berjalan secara berkala.</p>
                </div>
                <button @click="openCreate()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Baru
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Total Aktif</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalActiveThisMonth }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Komitmen Bulanan</p>
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">Rp {{ number_format($monthlyCommitment, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-5 sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Total Schedule</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $recurrings->total() }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            @if ($recurrings->isEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum Ada Transaksi Berulang</h3>
                    <p class="text-gray-500 dark:text-slate-400 mb-6">Mulai otomatisasi keuangan Anda dengan menambahkan jadwal baru.</p>
                    <button @click="openCreate()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Jadwal Pertama
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($recurrings as $recurring)
                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-slate-700 hover:shadow-md transition-all duration-200 p-5 space-y-4">
                            <div class="flex justify-between items-start">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($recurring->type === 'income')
                                        bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                    @else
                                        bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                    @endif">
                                    {{ $recurring->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                </span>
                                <button @click="toggleActive({{ $recurring->id }}, {{ $recurring->is_active ? 'true' : 'false' }})"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                                        {{ $recurring->is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-slate-600' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $recurring->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </div>

                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $recurring->title }}</h3>
                                <p class="text-sm text-gray-500 dark:text-slate-400">
                                    {{ $recurring->category?->name ?? 'Tanpa kategori' }} &bull; {{ $recurring->wallet?->name ?? 'Tanpa dompet' }}
                                </p>
                            </div>

                            <div class="text-xl font-bold {{ $recurring->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $recurring->type === 'income' ? '+' : '-' }} Rp {{ number_format($recurring->amount, 0, ',', '.') }}
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    {{ $recurring->frequency === 'daily' ? 'Harian' : ($recurring->frequency === 'weekly' ? 'Mingguan' : ($recurring->frequency === 'monthly' ? 'Bulanan' : 'Tahunan')) }}
                                </span>
                                @if($recurring->requires_confirmation)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        Konfirmasi
                                    </span>
                                @endif
                                @if(!$recurring->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-400">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>

                            <div class="flex gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                                <button @click="openEdit({{ json_encode([
                                    'id' => $recurring->id,
                                    'wallet_id' => $recurring->wallet_id,
                                    'title' => $recurring->title,
                                    'type' => $recurring->type,
                                    'amount' => $recurring->amount,
                                    'category_id' => $recurring->category_id,
                                    'frequency' => $recurring->frequency,
                                    'schedule_config' => $recurring->schedule_config,
                                    'start_date' => $recurring->start_date,
                                    'end_date' => $recurring->end_date,
                                    'is_active' => $recurring->is_active,
                                    'requires_confirmation' => $recurring->requires_confirmation
                                ]) }})"
                                        class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                    Edit
                                </button>
                                <button @click="confirmDelete({{ $recurring->id }})"
                                        class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $recurrings->links() }}
                </div>
            @endif
        </div>

        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity"
                     aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-black opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-full sm:max-w-lg">

                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="modalMode === 'create' ? 'Tambah Transaksi Berulang' : 'Edit Transaksi Berulang'"></h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveRecurring()" class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Dompet *</label>
                            <select x-model="form.wallet_id"
                                    class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih Dompet</option>
                                @foreach($wallets as $wallet)
                                    <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                @endforeach
                            </select>
                            <template x-if="errors.wallet_id">
                                <p class="text-red-500 text-xs mt-1" x-text="errors.wallet_id[0]"></p>
                            </template>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Judul *</label>
                            <input type="text" x-model="form.title" placeholder="Contoh: Langganan Netflix"
                                   class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <template x-if="errors.title">
                                <p class="text-red-500 text-xs mt-1" x-text="errors.title[0]"></p>
                            </template>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tipe</label>
                                <select x-model="form.type"
                                        class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="expense">Pengeluaran</option>
                                    <option value="income">Pemasukan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Jumlah (Rp) *</label>
                                <input type="text" x-model="form.amount" placeholder="Rp 0"
                                       @input="form.amount = formatCurrency($event.target.value)"
                                       class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 idr-input">
                                <template x-if="errors.amount">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.amount[0]"></p>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Kategori (Opsional)</label>
                            <select x-model="form.category_id"
                                    class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Frekuensi *</label>
                            <select x-model="form.frequency"
                                    @change="form.schedule_config = form.frequency === 'weekly' ? { day_of_week: [1] } : { day_of_month: 1 }"
                                    class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                            <template x-if="errors.frequency">
                                <p class="text-red-500 text-xs mt-1" x-text="errors.frequency[0]"></p>
                            </template>
                        </div>

                        <template x-if="form.frequency === 'weekly'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Pilih Hari *</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(day, index) in weekdays" :key="index">
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer transition-colors"
                                               :class="form.schedule_config.day_of_week?.includes(index + 1)
                                                   ? 'bg-indigo-100 dark:bg-indigo-900 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-200'
                                                   : 'bg-white dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:border-indigo-300'">
                                            <input type="checkbox"
                                                   x-model="form.schedule_config.day_of_week"
                                                   :value="index + 1"
                                                   class="sr-only">
                                            <span class="text-sm font-medium" x-text="day"></span>
                                        </label>
                                    </template>
                                </div>
                                <template x-if="errors.schedule_config">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.schedule_config[0]"></p>
                                </template>
                            </div>
                        </template>

                        <template x-if="form.frequency === 'monthly'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Pilih Tanggal *</label>
                                <div class="grid grid-cols-7 gap-1">
                                    <template x-for="date in 31" :key="date">
                                        <button type="button"
                                                @click="form.schedule_config.day_of_month = date"
                                                class="w-9 h-9 rounded-lg text-sm font-medium border transition-colors"
                                                :class="form.schedule_config.day_of_month === date
                                                   ? 'bg-indigo-600 text-white border-indigo-600'
                                                   : 'bg-white dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600'">
                                            <span x-text="date"></span>
                                        </button>
                                    </template>
                                </div>
                                <template x-if="errors.schedule_config">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.schedule_config[0]"></p>
                                </template>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tanggal Mulai *</label>
                                <input type="date" x-model="form.start_date"
                                       class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <template x-if="errors.start_date">
                                    <p class="text-red-500 text-xs mt-1" x-text="errors.start_date[0]"></p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tanggal Selesai (Opsional)</label>
                                <input type="date" x-model="form.end_date"
                                       class="w-full border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-between py-3 border-t border-b border-gray-100 dark:border-slate-700">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Butuh Konfirmasi</p>
                                <p class="text-sm text-gray-500 dark:text-slate-400">Tampilkan notifikasi sebelum diproses</p>
                            </div>
                            <button type="button" @click="form.requires_confirmation = !form.requires_confirmation"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                    :class="form.requires_confirmation ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-slate-600'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                      :class="form.requires_confirmation ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </div>
                    </form>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-900 flex justify-end gap-3">
                        <button @click="closeModal()"
                                class="px-4 py-2 text-sm text-gray-600 dark:text-slate-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium">
                            Batal
                        </button>
                        <button @click="saveRecurring()"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <button x-show="!showModal"
                @click="openCreate()"
                class="sm:hidden fixed bottom-24 right-6 w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg shadow-indigo-500/30 flex items-center justify-center transition-all z-40">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
        </button>
    </div>
</x-app-layout>
