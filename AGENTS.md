# AGENTS.md

CatatUang is a Laravel 13 personal finance tracker (multi-wallet, IDR currency).
PHP 8.3+, SQLite default, Tailwind + Alpine.js + Vite, Laravel Breeze auth.

## Commands

| Task | Command |
|------|---------|
| Full baseline (install + migrate:fresh --seed + test) | `composer init-session` |
| Run all tests | `php artisan test` |
| Run a single test file | `php artisan test tests/Feature/WalletSecurityTest.php` |
| Run a single test method | `php artisan test --filter=testGuestCannotAccessOtherWallet` |
| Lint / format (Laravel Pint) | `vendor/bin/pint` |
| Dev server (server + queue + pail + vite) | `composer dev` |
| Frontend build | `npm run build` |
| Fresh migrate + seed | `php artisan migrate:fresh --seed` |

**Order when implementing**: lint (`vendor/bin/pint`) → test (`php artisan test`) → commit.

`npm run build` requires Node.js v18+.

## Architecture

- **Domain model**: `User` → many `Wallets` → many `Transactions`. `Category` is shared.
  Transaction amounts update wallet `balance` inside `DB::transaction()`.
- **Authorization**: `WalletPolicy` and `TransactionPolicy` enforce per-user ownership.
  Never skip policy checks when adding routes.
- **Validation**: Form Request classes (`StoreTransactionRequest`, `UpdateTransactionRequest`),
  not inline controller validation.
- **Blade components**: `resources/views/components/select.blade.php` and
  `wallet-select.blade.php` are custom Alpine.js dropdowns replacing native `<select>`.
  Do not reintroduce native `<select>` elements.
- **Layouts**: `App\_view\Components\AppLayout` and `GuestLayout` are class-based Blade components.
- **Routes**: `routes/web.php` — resource controllers for `transactions` and `wallets`,
  auth via `routes/auth.php` (Breeze scaffolding).
- **SPA API** (`routes/api.php`): Laravel Sanctum token-based API with `api.` route prefix.
  Controllers in `app/Http/Controllers/Api/`.

### SPA API Endpoints

| Route | Method | Auth | Description |
|-------|--------|------|-------------|
| `/api/auth/login` | POST | - | Login, returns `{user, token}` |
| `/api/auth/register` | POST | - | Register, returns `{user, token}` |
| `/api/auth/logout` | POST | Sanctum | Logout (deletes current token) |
| `/api/auth/me` | GET | Sanctum | Get current user |
| `/api/wallets` | GET/POST | Sanctum | List/Create wallets |
| `/api/wallets/{id}` | GET/PUT/DELETE | Sanctum | Show/Update/Delete wallet |
| `/api/transactions` | GET/POST | Sanctum | List/Create transactions |
| `/api/transactions/{id}` | GET/PUT/DELETE | Sanctum | Show/Update/Delete transaction |
| `/api/transactions/export` | GET | Sanctum | Export CSV |
| `/api/recurring-transactions` | GET/POST | Sanctum | List/Create recurring |
| `/api/recurring-transactions/{id}` | GET/PUT/DELETE | Sanctum | Show/Update/Delete recurring |
| `/api/recurring-transactions/{id}/toggle` | PATCH | Sanctum | Toggle active status |
| `/api/categories` | GET | Sanctum | List categories |
| `/api/dashboard` | GET | Sanctum | Dashboard summary |

**Note**: API routes use `api.*` prefix to avoid collision with web routes (e.g., `api.transactions.index` vs `transactions.index`).

## Testing

- Tests use **SQLite in-memory** (`:memory:`) — no external DB needed.
- Cache, session, queue are set to `array` in `phpunit.xml`.
- Feature tests: `tests/Feature/` (auth, profile, transaction CRUD, wallet security, export).
- Unit tests: `tests/Unit/` (transaction logic).
- Key security tests: `WalletSecurityTest.php` — wallet isolation and amount validation.

## Deployment

- **Railway** (`railway.json`): railpack builder, SQLite, `php artisan serve --port=$PORT`.
- **Dockerfile**: two-stage (Node 22 frontend → PHP 8.4 backend), auto-migrates + seeds on start.
- **Procfile**: copies `.env.production` → `.env` then migrates + serves.
  Note: `.env.production` is **gitignored** — must be provisioned on the deploy target.
- Migrations run automatically on deploy (`--force`); seeding also runs in Docker.

## Repo Artifacts

- `feature.json` — feature tracker (source of truth for feature status, not `feature_list.json`).
- `progress.md` — session log and current verified state.
- `conventions.md` — Laravel coding conventions (in Indonesian; follow these for code style).

## Working Rules

- Read `progress.md` and `feature.json` before starting; fix broken baseline first.
- Follow `conventions.md` for all PHP code (fat models, thin controllers, Form Requests,
  Eloquent over raw SQL, no logic in routes).
- Work on one feature at a time; don't mark complete without passing tests.
- After implementing: run `vendor/bin/pint` then `php artisan test`.
- Update `progress.md` and `feature.json` before ending a session.