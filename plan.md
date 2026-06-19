---
title: Project Plan - Refactoring CatatUang ke Web-First Analytics & Multi-Wallet
status: Active
owner: Product Engineer
tech_stack: [Laravel 11, Tailwind CSS, Laravel Breeze, PostgreSQL, PHPUnit]
---

# Project Plan: Web-First Analytics Dashboard & Multi-Wallet System

## 1. Ringkasan Eksekutif (Objective)
> **Goal Utama:** Mengubah layout CatatUang dari yang sebelumnya bertumpuk (mobile-first) menjadi tampilan horizontal berbasis dasbor analitik web yang kaya informasi, serta menambahkan fitur multi-wallet per pengguna.

* **KPI / Tolok Ukur Keberhasilan:**
  * Layout memanfaatkan lebar layar desktop secara optimal (gaya dasbor admin/analitik) tanpa merusak tampilan saat di-scale down ke mobile.
  * Pengguna dapat berganti konteks data dasbor secara instan hanya dengan mengubah dropdown pilihan Wallet.
  * Kode program bersih dari redundansi (Modul `Expense` lama dihapus total, beralih penuh ke `Transaction`).
* **Ruang Lingkup (In Scope):**
  * Penghapusan (Cleanup) total komponen `Expense` (Model, Controller, Migration, Views, Routes).
  * Pembuatan sistem Multi-Wallet (1 User -> Banyak Wallets).
  * Penambahan Dropdown Selector Wallet pada Dashboard Utama untuk memuat data secara dinamis.
  * Rekayasa ulang layout menjadi 2-3 Kolom horizontal ala Dasbor Analitik (Komponen Utama berdampingan dengan Form Input).
* **Di Luar Lingkup (Out of Scope):**
  * Transfer saldo antar wallet (fokus pada pencatatan mandiri per wallet terlebih dahulu).

## 2. Batasan & Prinsip Desain (Constraints)
> 💡 **Aturan Main:** Konsisten dengan komponen estetika **Laravel Breeze** (menggunakan palette warna `gray-100`/`white`, border tipis, dan efek shadow halus). Gunakan pendekatan grid desktop (`md:grid-cols-3` atau `lg:grid-cols-4`) untuk menyebar informasi secara horizontal. **Validasi input wajib memastikan bahwa `wallet_id` yang dikirim benar-benar milik user yang sedang login.**

---

## 3. Breakdown Tugas (Task Breakdown)

### Fase 1: Pembersihan Kode Modul Lama (Deprecating Expense)
- [x] Hapus file Model `Expense.php`.
- [x] Hapus `ExpenseController.php` beserta Form Requests terkait.
- [x] Hapus folder views `resources/views/expenses/`.
- [x] Hapus semua route yang mengarah ke `/expenses` di `routes/web.php`.
- [x] Buat file migrasi baru untuk melakukan `DROP TABLE IF EXISTS expenses` guna membersihkan PostgreSQL di production.

### Fase 2: Perancangan Basis Data Baru (Multi-Wallet Setup)
- [x] Buat Migration & Model untuk `Wallet` (Atribut: `id`, `user_id`, `name`, `balance`, `timestamps`).
- [x] Buat Migration untuk memodifikasi tabel `transactions`:
  * Tambahkan kolom `wallet_id` (`bigint FK to wallets, cascade on delete`).
- [x] Definisikan Relasi Eloquent baru:
  * `User` -> `hasMany(Wallet::class)`
  * `Wallet` -> `hasMany(Transaction::class)` & `belongsTo(User::class)`
  * `Transaction` -> `belongsTo(Wallet::class)`

### Fase 3: Logika Bisnis & Wallet Context Switcher (Backend)
- [x] Perbarui `TransactionController@index` untuk menerima query parameter `wallet_id` (jika kosong, default ke wallet pertama milik user).
- [x] Sesuaikan logika kalkulasi Ringkasan Finansial (In, Out, Saldo) di Controller agar otomatis terfilter berdasarkan `wallet_id` yang dipilih.
- [x] Perbarui `StoreTransactionRequest` untuk memvalidasi `wallet_id`: `required|exists:wallets,id`. Tambahkan rule kustom untuk memastikan wallet tersebut milik `auth()->id()`.
- [x] Implementasikan database transaction (`DB::transaction`) saat menyimpan transaksi baru agar nominal `amount` otomatis meng-update kolom `balance` di tabel `wallets`.

### Fase 4: Slicing UI Web-First Analytics Layout (Frontend)
- [x] **Top Row (Header & Context):** Buat baris atas berisi Judul Dashboard, Dropdown Selector Wallet, dan Tombol shortcut pemicu form.
- [x] **Middle Row (Consolidated Metrics):** Sebar komponen Pemasukan, Pengeluaran, dan Sasa Saldo secara horizontal (Grid 3 kolom).
- [x] **Bottom Row (Analytics Split Grid):** Buat layout horizontal terpisah:
  * **Sisi Kiri (Porsi 1/3):** Form input transaksi manual (termasuk dropdown pilihan wallet tujuan transaksi).
  * **Sisi Kanan (Porsi 2/3):** Area filter pencarian tabular dan tabel riwayat transaksi terpusat.

---

## 4. Referensi Arsitektur UI (Web-Friendly Analytics Dashboard)

Berikut adalah cetak biru komponen Blade + Tailwind CSS yang mengimplementasikan layout dasbor horizontal berbasis tema Laravel Breeze:

```html
<x-app-layout>
    <div class="py-10 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-xl shadow-sm border border-gray-200/60 gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Dashboard Analitik Finansial</h2>
                    <p class="text-xs text-gray-500 mt-1">Pantau ringkasan pemasukan, pengeluaran, dan manajemen dompet Anda.</p>
                </div>
                
                <div class="w-full sm:w-auto flex items-center space-x-3">
                    <form method="GET" action="{{ route('transactions.index') }}" id="wallet-switcher-form" class="flex items-center gap-2">
                        <label for="wallet_context" class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Dompet:</label>
                        <select id="wallet_context" name="wallet_id" onchange="document.getElementById('wallet-switcher-form').submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-gray-50">
                            @foreach($wallets as $wallet)
                                <option value="{{ $wallet->id }}" {{ request('wallet_id') == $wallet->id ? 'selected' : '' }}>
                                    {{ $wallet->name }} (Rp {{ number_format($wallet->balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 text-center">
                    <div class="p-6 hover:bg-gray-50/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 mb-2">Total Pemasukan</span>
                        <span class="block text-3xl font-extrabold text-green-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-6 hover:bg-gray-50/50 transition">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 mb-2">Total Pengeluaran</span>
                        <span class="block text-3xl font-extrabold text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-6 bg-indigo-50/30">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mb-2">Saldo Dompet Saat Ini</span>
                        <span class="block text-3xl font-extrabold text-indigo-600">Rp {{ number_format($activeWalletBalance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200/60 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Catat Transaksi Baru</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Input data pemasukan atau pengeluaran secara manual.</p>
                    </div>

                    <form method="POST" action="{{ route('transactions.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="form_wallet" :value="__('Simpan ke Dompet')" />
                            <select id="form_wallet" name="wallet_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                @foreach($wallets as $wallet)
                                    <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="form_type" :value="__('Tipe')" />
                                <select id="form_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    <option value="Expense">Pengeluaran</option>
                                    <option value="Income">Pemasukan</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="amount" :value="__('Nominal (Rp)')" />
                                <x-text-input id="amount" name="amount" type="number" class="mt-1 block w-full text-sm" required placeholder="0" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="category" :value="__('Kategori')" />
                                <select id="category" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="transaction_at" :value="__('Tanggal')" />
                                <x-text-input id="transaction_at" name="transaction_at" type="date" class="mt-1 block w-full text-sm" required :value="now()->format('Y-m-d')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Keterangan / Catatan')" />
                            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Catatan tambahan..."></textarea>
                        </div>

                        <x-primary-button class="w-full justify-center py-2.5 text-sm">
                            {{ __('Simpan Transaksi') }}
                        </x-primary-button>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200/60">
                        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-4 text-sm">
                            <input type="hidden" name="wallet_id" value="{{ request('wallet_id') }}">
                            <div class="flex-1 min-w-[200px]">
                                <x-text-input id="search" name="search" type="text" class="block w-full text-sm" :value="request('search')" placeholder="Cari kata kunci deskripsi..." />
                            </div>
                            <div>
                                <x-primary-button class="py-2 px-4">
                                    {{ __('Cari') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-sm text-gray-700 uppercase tracking-wider">Log Transaksi Berjalan</h3>
                            <div class="flex space-x-2">
                                <a href="#" class="px-2.5 py-1 text-xs font-semibold bg-white border border-gray-200 text-gray-600 rounded-md hover:bg-gray-50 shadow-sm">Excel</a>
                                <a href="#" class="px-2.5 py-1 text-xs font-semibold bg-white border border-gray-200 text-gray-600 rounded-md hover:bg-gray-50 shadow-sm">PDF</a>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50/70">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Kategori</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Keterangan</th>
                                        <th class="px-6 py-3 text-right font-semibold text-gray-600">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">19 Jun 2026</td>
                                        <td class="px-6 py-4 text-gray-900 font-medium">Gaji Bulanan</td>
                                        <td class="px-6 py-4 text-gray-500">Transfer vendor utama</td>
                                        <td class="px-6 py-4 text-right font-bold text-green-600 whitespace-nowrap">+ Rp 5.000.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>