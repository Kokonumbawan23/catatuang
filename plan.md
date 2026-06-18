---
title: Project Plan - Aplikasi Pencatatan Pengeluaran Pribadi (CatatUang)
status: Active
owner: Product Engineer
duration: 1 Week (7 Days)
tech_stack: [Laravel 11, Blade/Tailwind CSS, MySQL/SQLite]
---

# Project Plan: CatatUang Web App

## 1. Ringkasan Eksekutif (Objective)
> **Goal Utama:** Membangun aplikasi web pencatatan pengeluaran pribadi yang super cepat, minimalis, dan responsive (nyaman dibuka di HP maupun Desktop) dalam waktu 7 hari menggunakan Laravel.

* **KPI / Tolok Ukur Keberhasilan:**
  * Pengguna bisa mencatat pengeluaran kurang dari 5 detik.
  * Tampilan *mobile-first* (bersih, tanpa sidebar yang penuh di HP, navigasi bawah/bottom nav jika diperlukan).
  * Aplikasi dideploy dan bisa diakses online di akhir minggu.
* **Ruang Lingkup (In Scope):**
  * Autentikasi simpel (Login & Register).
  * CRUD Pengeluaran (Nominal, Kategori, Catatan, Tanggal).
  * Dashboard ringkasan total pengeluaran bulanan & grafik simpel.
  * Desain UI minimalis berorientasi mobile.
* **Di Luar Lingkup (Out of Scope):**
  * Multi-currency (hanya mendukung IDR).
  * Pencatatan Pemasukan (fokus ke pengeluaran dulu agar selesai 1 minggu).
  * Eksport ke Excel/PDF.

## 2. Batasan & Prinsip Desain (Constraints)
> 💡 **Aturan Main:** Jangan habiskan waktu membuat UI desktop yang kompleks. Mulai desain dari ukuran layar HP (Mobile-first). **Hindari sidebar** untuk versi mobile; gunakan menu *dropdown* minimalis atau *bottom navigation bar* agar fokus pada data.

* **Arsitektur:** Monolith Laravel dengan Blade Templating + Tailwind CSS untuk mempercepat slicing UI tanpa pusing setup framework JS terpisah.
* **Database:** Gunakan SQLite untuk lokal agar setup cepat, dan MySQL/PostgreSQL untuk production.

## 3. Garis Waktu & Fase Eksekusi (7-Day Roadmap)

| Hari | Fase / Aktivitas Utama | Output | Status |
| :--- | :--- | :--- | :--- |
| **Hari 1** | Analisis Data & Perancangan Database | Kejelasan Tabel & Relasi | Selesai |
| **Hari 2** | Setup Project & Autentikasi | Fitur Login/Register Selesai | Selesai |
| **Hari 3** | Fitur Inti (Backend CRUD Pengeluaran) | API/Controller & Logic Selesai | Selesai |
| **Hari 4** | Slicing UI Frontend (Responsive Web) | Tampilan Dashboard & Form di HP/PC | Selesai |
| **Hari 5** | Fitur Tambahan (Grafik & Filter Kategori) | Halaman Visualisasi Pengeluaran | Selesai |
| **Hari 6** | Bug Fixing, Refactoring, & Testing | Aplikasi Stabil & Ringan | Selesai |
| **Hari 7** | Deployment (Railway) | Aplikasi Live dengan SSL | Selesai |

## 4. Breakdown Tugas Harian (Task Checklist)

### Hari 1: Desain Basis Data & Persiapan
- [x] Tentukan kategori pengeluaran default (Makanan, Transportasi, Tagihan, Hiburan, dll).
- [x] Rancang skema database (Tabel `users`, `categories`, `expenses`).
- [x] Buat wireframe coret-coretan layout form input di HP.

### Hari 2: Pondasi Proyek
- [x] Initialize project Laravel baru.
- [x] Install Laravel Breeze (untuk scaffolding login/register cepat dengan Tailwind).
- [x] Jalankan migrasi database awal.

### Hari 3: Logika Bisnis (Backend)
- [x] Buat Migration, Model, dan Controller untuk `Expense` dan `Category`.
- [x] Implementasikan relasi (User `hasMany` Expense, Expense `belongsTo` Category).
- [x] Buat validation rules (Nominal wajib angka, tanggal wajib diisi).

### Hari 4: Antarmuka Pengguna (Frontend Mobile-First)
- [x] Desain halaman Dashboard (Menampilkan total pengeluaran bulan ini).
- [x] Desain Form Tambah Pengeluaran yang jempol-friendly (mudah ditekan di HP).
- [x] Slicing daftar riwayat pengeluaran (paling baru ada di atas).

### Hari 5: Visualisasi & Optimasi
- [x] Integrasikan Chart.js (via CDN) untuk menampilkan diagram lingkaran kategori pengeluaran.
- [x] Tambahkan fitur filter pengeluaran berdasarkan bulan berjalan.

### Hari 6 & 7: Pembersihan & Peluncuran
- [x] Test input data di layar HP (cek jika ada overflow atau teks kepotong).
- [x] Deploy aplikasi ke layanan hosting/VPS (Railway).
- [x] Setup SSL (Railway auto-SSL).

## 5. Referensi Teknis & Struktur Data

### Skema Tabel `expenses` (Rencana)
```json
{
  "id": "bigint (PK)",
  "user_id": "bigint (FK to users)",
  "category_id": "bigint (FK to categories)",
  "amount": "decimal(15,2)",
  "description": "text (nullable)",
  "spent_at": "date",
  "timestamps": "true"
}
