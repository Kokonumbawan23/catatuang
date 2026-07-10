# TASK: Buat Komponen UI Responsive untuk Fitur Transaksi Berulang (CatatUang)

Kamu adalah seorang Senior Frontend Engineer / UI Developer. Buatlah tampilan web yang responsive, minimalis, dan estetik untuk fitur "Pencatatan Berulang Otomatis" sesuai dengan panduan berikut.

## Tech Stack & Style Panduan
* Gunakan framework/library sesuai proyek saat ini: [Sebutkan: Vue.js / React / Tailwind CSS / Plain HTML-CSS].
* Desain harus **Bebas Sidebar** (Focused View), menggunakan Top Navbar untuk desktop dan Bottom Navbar untuk mobile.
* Implementasikan fitur Dark Mode toggle jika proyek saat ini mendukungnya.

## Komponen yang Harus Dibuat:
1. **Header & Summary Card:** Menampilkan judul halaman "Transaksi Berulang" dan ringkasan total komitmen transaksi rutin bulan ini dalam bentuk kartu yang bersih.
2. **Tombol "Tambah Jadwal Baru":** Berada di posisi yang mudah dijangkau (di mobile, bisa berupa Floating Action Button / FAB).
3. **Form Modal (Dynamic Input):** * Buat form yang interaktif. Jika user memilih frekuensi 'Weekly', tampilkan pilihan Hari (Senin-Minggu). Jika memilih 'Monthly', tampilkan pilihan Tanggal (1-31).
   * Tambahkan toggle switch untuk `requires_confirmation` (Butuh konfirmasi).
4. **List Cards (Responsive Grid):**
   * Desktop: Tampilkan dalam bentuk grid 2 atau 3 kolom.
   * Mobile: Tampilkan dalam bentuk list 1 kolom vertikal yang memenuhi layar.
   * Setiap kartu harus memiliki tombol Toggle untuk mengaktifkan/nonaktifkan jadwal (`is_active`), informasi nama transaksi, kategori, nominal, dan badge frekuensi.

## Efek & Interaksi (UX):
* Tambahkan efek hover halus pada kartu transaksi.
* Berikan animasi transisi (fade-in/slide-over) saat modal form dibuka.
* Pastikan semua input form memiliki state *validation error* yang jelas jika user mengosongkan kolom penting.

Silakan mulai dengan membuat komponen List Cards terlebih dahulu untuk menampilkan data mock, kemudian lanjutkan ke pembuatan Form Modal.