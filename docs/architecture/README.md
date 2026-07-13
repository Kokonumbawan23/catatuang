# CatatUang - Dokumentasi Arsitektur

Dokumentasi ini menjelaskan arsitektur dan implementasi setiap fitur dalam aplikasi CatatUang.

## Daftar Fitur

### Core Features
- [01-wallet-management.md](./01-wallet-management.md) - Multi-Wallet System
- [02-transaction-management.md](./02-transaction-management.md) - Transaksi & Konsolidasi
- [03-dashboard-analytics.md](./03-dashboard-analytics.md) - Dashboard & Analytics
- [04-recurring-transactions.md](./04-recurring-transactions.md) - Transaksi Berulang
- [05-balance-alert.md](./05-balance-alert.md) - Balance Alert & Progress Bar

### System Features
- [06-authentication.md](./06-authentication.md) - Authentication (Breeze + Sanctum)
- [07-spa-frontend.md](./07-spa-frontend.md) - Vue 3 SPA Architecture
- [08-export-csv.md](./08-export-csv.md) - Export CSV
- [09-dark-mode.md](./09-dark-mode.md) - Dark Mode Toggle
- [10-security-tests.md](./10-security-tests.md) - Security Tests
- [11-input-ui-fixing.md](./11-input-ui-fixing.md) - Input UI Fixing
- [12-dropdown-selector.md](./12-dropdown-selector.md) - Custom Dropdown Selector

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13 |
| Frontend | Vue 3 SPA + Tailwind CSS + Pinia + Vue Router |
| Auth (API) | Laravel Sanctum |
| Database | SQLite (dev) / PostgreSQL (prod) |
| API | RESTful JSON |

## Database Schema

```
┌─────────────┐     ┌─────────────┐     ┌─────────────────┐
│    User     │────▶│   Wallet    │────▶│  Transaction     │
│             │     │             │     │                 │
│ id          │     │ id          │     │ id              │
│ name        │     │ user_id     │     │ wallet_id       │
│ email       │     │ name        │     │ category_id     │
│ password    │     │ balance     │     │ type (in/out)   │
└─────────────┘     │ balance_    │     │ amount          │
                    │ limit       │     │ description     │
                    └─────────────┘     │ transaction_date │
                              │         └─────────────────┘
                              │
                         ┌────────────────┐     ┌─────────────┐
                         │RecurringTrans..│────▶│  Category  │
                         │                │     │             │
                         │ wallet_id      │     │ id          │
                         │ category_id    │     │ name        │
                         │ type           │     │ color       │
                         │ amount         │     │ icon        │
                         │ frequency      │     └─────────────┘
                         │ is_active     │
                         │ start_date    │
                         │ end_date      │
                         └────────────────┘
```

## API Routes

### Authentication (api.php)
```
POST   /api/auth/login      - Login (returns user + token)
POST   /api/auth/register  - Register (returns user + token)
POST   /api/auth/logout    - Logout (requires Sanctum)
GET    /api/auth/me         - Get current user (requires Sanctum)
```

### Resources (api.php, Sanctum required)
```
GET    /api/wallets                    - List user's wallets
POST   /api/wallets                    - Create wallet
GET    /api/wallets/{id}               - Get wallet
PUT    /api/wallets/{id}               - Update wallet
DELETE /api/wallets/{id}               - Delete wallet

GET    /api/transactions               - List transactions
POST   /api/transactions               - Create transaction
PUT    /api/transactions/{id}           - Update transaction
DELETE /api/transactions/{id}           - Delete transaction
GET    /api/transactions/export        - Export CSV

GET    /api/recurring-transactions    - List recurring
POST   /api/recurring-transactions    - Create recurring
PUT    /api/recurring-transactions/{id} - Update recurring
DELETE /api/recurring-transactions/{id} - Delete recurring
PATCH  /api/recurring-transactions/{id}/toggle - Toggle active

GET    /api/categories               - List categories
GET    /api/dashboard                - Dashboard summary
```

## Security

- Semua API resource routes dilindungi oleh `auth:sanctum` middleware
- WalletPolicy memastikan user hanya bisa akses wallet miliknya
- TransactionPolicy memastikan user hanya bisa akses transaksi miliknya
- RecurringTransactionPolicy membatasi akses berdasarkan ownership
- Validasi nominal transaksi memastikan amount > 0

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/WalletSecurityTest.php

# Run with coverage
php artisan test --coverage
```

Test coverage meliputi:
- Wallet isolation antar user
- Transaction validation (amount > 0)
- Recurring transaction processing
- Authentication flow
- Export functionality
