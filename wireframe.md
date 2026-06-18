# Mobile Wireframe - CatatUang

## Layout Form Input Pengeluaran (Mobile-First)

```
┌─────────────────────────────┐
│  ← Catat Pengeluaran        │
├─────────────────────────────┤
│                             │
│  ┌───────────────────────┐  │
│  │    Rp. 0               │  │
│  │    [Nominal Input]     │  │
│  └───────────────────────┘  │
│                             │
│  Kategori                   │
│  ┌───────────────────────┐  │
│  │ 🍔 Makanan        ▼    │  │
│  └───────────────────────┘  │
│                             │
│  Tanggal                    │
│  ┌───────────────────────┐  │
│  │ 📅 18 Jun 2026        │  │
│  └───────────────────────┘  │
│                             │
│  Catatan (opsional)         │
│  ┌───────────────────────┐  │
│  │                       │  │
│  │                       │  │
│  └───────────────────────┘  │
│                             │
│  ┌───────────────────────┐  │
│  │                       │  │
│  │   💾 SIMPAN           │  │
│  │                       │  │
│  └───────────────────────┘  │
│                             │
├─────────────────────────────┤
│  🏠    📊    ➕    👤        │
│  Home  Stats  Add  Profile  │
└─────────────────────────────┘
```

## Dashboard (Mobile)

```
┌─────────────────────────────┐
│  CatatUang          👤      │
├─────────────────────────────┤
│                             │
│  Juni 2026                  │
│  ┌───────────────────────┐  │
│  │ Total Pengeluaran      │  │
│  │ Rp. 2.450.000         │  │
│  └───────────────────────┘  │
│                             │
│  Ringkasan Kategori         │
│  🍔 Makanan    Rp. 850.000  │
│  🚗 Transport  Rp. 400.000  │
│  📄 Tagihan    Rp. 600.000  │
│  🎬 Hiburan    Rp. 300.000  │
│  🛒 Belanja    Rp. 300.000  │
│                             │
│  ── Riwayat Terakhir ──     │
│                             │
│  🍔 Ayam Geprek      Rp.25.000│
│     18 Jun 2026             │
│                             │
│  🚗 Grab ke Kantor  Rp.15.000│
│     18 Jun 2026             │
│                             │
│  📄 Token Listrik   Rp.150.000│
│     17 Jun 2026             │
│                             │
├─────────────────────────────┤
│  🏠    📊    ➕    👤        │
│  Home  Stats  Add  Profile  │
└─────────────────────────────┘
```

## Design Notes

- **Bottom Navigation**: 4 tabs (Home, Stats, Add, Profile)
- **Input Form**:
  - Large touch-friendly input (min 48px height)
  - Dropdown kategori dengan icon
  - Date picker native mobile
  - Simpan button full-width di bawah
- **No Sidebar**: Mobile-first, sidebar hanya muncul di desktop
- **Font Size**: Minimum 16px untuk input (mencegah zoom iOS)
