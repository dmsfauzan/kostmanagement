# Kost Management System

Production-ready Kost Management System built on **Laravel 13** — Livewire, Blade, Tailwind CSS, and MySQL.

> **Phase 1 — Foundation** is ready. The operational phases that follow will not break what is already runnable.

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 · PHP 8.3 |
| Frontend | Livewire 3 + Blade + Tailwind CSS 3 + Vite |
| Auth | Laravel Breeze (Livewire / Volt) |
| Authorization | [spatie/laravel-permission](https://github.com/spatie/laravel-permission) + Policies + per-route/middleware `role:`/`active` |
| Database | MySQL 8 · Laragon-friendly `kost_management` DB |
| Queues / Cache | `database` driver (no Redis required at this stage) |
| Notifications | `database` + `log` mail |
| Testing | PHPUnit + Pest (incl. Pest 4, Pest Laravel) |

> Monetary values are stored as whole-rupiah `bigInteger` values (no floats).

## Features by Phase

| Phase | What landed | Status |
|---|---|---|
| **0 — Architecture** | System, DB, ERD, module map, route map, permission matrix, service & scheduler plan | ✅ Delivered |
| **1 — Foundation** | Auth, User, roles/permissions, admin/tenant/public layouts, 12 reusable UI components, settings, audit, dashboards, seeders, test suite | ✅ **Ready — you are here** |
| **2 — Property & Room Management** | properties, buildings, floors, room types, rooms, amenities, room map, public rooms site | ✅ **Ready — you are here** |
| **3 — Tenant & Lease** | tenants, documents, invitation/activation, leases, move-in/out, deposit settlement | ✅ **Ready** |
| **4A — Billing Foundation** | invoices, invoice items, calculation services, manual/admin CRUD, tenant portal, issue notifications | ✅ **Ready** |
| **4B — Recurring Billing** | monthly generation + idempotency, overdue marking, late fees, scheduler | ✅ **Ready** |
| **5A — Payment Foundation** | payment methods, proof upload, verify/reject, invoice recalculation, refund/cancel | ✅ **Ready — you are here** |
| **5B — Payment Polish** | additional payment UX refinements | 🔜 Next |
| **5 — Payment** | payment methods, proof upload/verification, invoice recalculation | Planned |
| **6 — Maintenance** | tickets, status workflow, assignment, SLA | Planned |
| **7 — Announcement & Notification** | announcements, database+mail notifications, reminder scheduler | Planned |
| **8 — Expense & Reporting** | expenses, financial/occupancy dashboards, reports + exports | Planned |
| **9 — Security & Optimization** | IDOR review, N+1, caching, rate limits, private-file endpoints | Planned |
| **10 — Production Readiness** | backup, queue/scheduler ops, deployment notes, full coverage | Planned |

## Requirements

- PHP ≥ 8.3
- Composer 2
- Node ≥ 18, `npm`
- MySQL 8
- Laragon (or any MySQL-backed PHP 8.3 dev environment)

## Installation

```bash
git clone https://github.com/dmsfauzan/kostmanagement.git kostmanagement
cd kostmanagement
composer install
cp .env.example .env
php artisan key:generate
```

Configure `DB_*` in `.env` (database `kost_management` is the default):

```env
APP_NAME="Kost Management"
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kost_management
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```bash
php artisan migrate --seed
npm install
npm run build
```

For dev (serves the app + re-compiles Tailwind live):

```bash
composer dev
# or individually: php artisan serve | npm run dev | php artisan queue:listen
```

### Local development with Laragon

This project lives in `C:\laragon\www\KostManagement`. Laragon's auto virtual hosts map Laravel folders to their
`public/` directory, so the app should be accessed at:

**http://kostmanagement.test** — with `APP_URL=http://kostmanagement.test` in `.env`.

> **Never** access the app through a sub-path such as `http://localhost/KostManagement/public`.
> Livewire loads its JavaScript from a root-relative URL (`/livewire/livewire.js`) which 404s under a
> sub-path. Without that script, `<form wire:submit>` falls back to a native GET submit — credentials
> leak into the URL (`.../login?email=...&password=...`) and login appears broken.

If `kostmanagement.test` does not resolve:

1. Laragon → **Stop All** → **Start All** (regenerates `etc/apache2/sites-enabled/auto.KostManagement.test.conf`
   and appends `127.0.0.1  KostManagement.test  #laragon magic!` to the hosts file).
2. Confirm `C:\Windows\System32\drivers\etc\hosts` contains that line (add it manually as admin if needed).
3. Restart and open `http://kostmanagement.test`.

Prefer the built-in server? Run `php artisan serve`, set `APP_URL=http://localhost:8000`, and open that
address instead.

## Seed Data

`DatabaseSeeder` fully wires:

- **RolePermissionSeeder** — creates the permission catalogue and roles `owner` / `admin` / `finance` / `technician` / `tenant`. Owner is given every ability via the `Gate::before` hook.
- **SettingsSeeder** — seeds a database-backed `Settings` table (currency, billing, reminder, maintenance, upload defaults). Runtime lookups are cached via `SettingsService`.
- **DevUserSeeder** — local/testing-only accounts (all with password `password` unless `KOST_DEV_PASSWORD` is set):

| Role | Email | Name |
|---|---|---|
| `owner` | `owner@kostmanagement.test` | Budi Pemilik |
| `admin` | `admin@kostmanagement.test` | Siti Admin |
| `finance` | `finance@kostmanagement.test` | Dewi Keuangan |
| `technician` | `technician@kostmanagement.test` | Agus Teknisi |
| `tenant` | `tenant@kostmanagement.test` | Rina Penghuni |

`DevUserSeeder` does nothing outside `local`/`testing` environments. Set `KOST_DEV_PASSWORD` in `.env` to override the development password without hardcoding it.

- **PropertySeeder** — local/testing-only. Creates **Kost Mawar** with 2 buildings, 3 floors, 3 room types (Standard/Deluxe/Premium), 11 amenities, and 30 rooms across all statuses (available/occupied/maintenance/reserved).
- **TenantSeeder** — local/testing-only. Links `tenant@kostmanagement.test` to a tenant profile and an **active lease** (room becomes occupied, deposit held), plus 2 extra sample tenants.
- **BillingSeeder** — local/testing-only. Issues an invoice for the sample active lease so the tenant portal shows a real bill.
- **PaymentMethodSeeder** — always seeded (reference data): 4 payment methods for every environment.
- **PaymentSeeder** — local/testing-only. Creates a pending payment on the sample invoice to test the verification flow.

## Roles & Permissions

Full catalogue (66 permissions across 20 modules):

```
dashboard.view,
property.view/create/update,
building.view/create/update/delete,
floor.view/create/update/delete,
room_type.view/create/update/delete,
room.view/create/update/delete,
amenity.view/create/update/delete,
tenant.view/create/update,
tenant_document.view/create/delete,
lease.view/create/update/terminate,
invoice.view/create/update/void,
payment.view/create/verify/reject,
expense.view/create/update/delete,
maintenance.view/create/update/assign/close,
announcement.view/create/update/delete,
report.view/export,
user.view/create/update/delete,
role.view/update,
settings.view/update,
audit.view
```

Role matrix (endpoints are filtered via `role:` middleware and `Gate::before` for owners):

| Ability | Owner | Admin | Finance | Technician | Tenant |
|---|---:|---:|---:|---:|---:|
| `dashboard.view` | + | + | + | + | + (own) |
| property / building / floor / room / amenity | + | + | – | room view | – |
| tenant / lease | + | + | view | view | own |
| invoice / payment | + | + | + (void) | – | own view/create |
| expense | + | view/create/update | + | – | – |
| maintenance | + | + | view | + (assign/close) | own create/view |
| announcement | + | + | – | – | view |
| report.* | + | + | + | – | – |
| user / role / audit | + | user view (+/- create) | – | – | – |
| settings.* | + | view | – | – | – |

Authorization is enforced at three layers: route middleware, spatie permissions, and (in later phases) Policies per model (record-level / IDOR guard).

## Routes

```
GET  /                               public.home
GET  /rooms                          public rooms (available only)
GET  /rooms/{slug}                   room detail (404 when not available)
GET  /facilities /about /rules /faq /contact
GET  /admin/dashboard                admin.dashboard   (auth + role:owner,admin,finance,technician)
GET  /admin/properties | buildings | floors | room-types | amenities | rooms
GET  /admin/rooms/map                visual room map
GET  /admin/tenants | tenants/create | tenants/{tenant} | tenants/{tenant}/edit
GET  /admin/leases | leases/create | leases/{lease} | leases/{lease}/edit | leases/{lease}/move-out
GET  /admin/tenant-documents/{document}/download   (authorized private file)
GET  /admin/invoices | invoices/create | invoices/{invoice_number}
GET  /admin/payments | payments/{payment} | payments/{payment}/proof
GET  /admin/payment-methods
GET  /tenant/activate/{user}         signed activation URL (from invite email)
GET  /tenant/lease                   tenant lease + history
GET  /tenant/invoices | invoices/{invoice}   tenant invoices (own only)
GET  /tenant/payments | payments/create | payments/{payment}/proof
GET  /tenant/notifications           tenant notification inbox
GET  /tenant/dashboard               tenant.dashboard  (auth + role:tenant)
GET  /dashboard                      → redirects to the correct dashboard for the authenticated role
GET  /profile                        authenticated user profile
... plus the full Breeze/Livewire auth set (/login, /register, /forgot-password, /reset-password, /verify-email, /confirm-password)
```

Every admin page is guarded by a `can:<module>.<action>` middleware, and mutating actions re-check the permission inside the component.

## Property & Room Management (Phase 2)

Data model: `properties → buildings → floors → rooms`, with `room_types`, `amenities` (+ `room_amenity` / `room_type_amenity` pivots) and `room_photos`.

- **Admin CRUD** (Livewire, class-based) for properties, buildings, floors, room types, amenities, and rooms.
- **Room list** offers a table **and** card view, plus search, status filter, and property/building filters.
- **Room map** (`/admin/rooms/map`) groups rooms by building → floor with colour-coded status tiles and an occupancy summary.
- **Public site** shows only `available` rooms on `/`, `/rooms`, and `/rooms/{slug}`.
- Business rules enforced: unique room number per property, floor must belong to the chosen building, price ≥ 0, unique building code per property, unique floor level per building.
- `RoomService` handles room writes (amenity sync, photo storage, audit logging) and `OccupancyService` computes per-status counts and occupancy rate.

## Tenant & Lease Management (Phase 3)

Data model: `tenants → leases → deposits`, plus `tenant_documents` (private disk) and `lease_status_histories`.

- **Tenant CRUD + portal account** (Livewire): personal info, emergency, vehicle, notes; status follows the account lifecycle (prospect → invited → active → moved_out).
- **Invitation**: creating a tenant with an email auto-provisions a `tenant`-role `User` (status inactive) and sends `TenantInvitationNotification` (mail + database record).
- **Activation**: temporary **signed URL** `/tenant/activate/{user}` → Livewire component sets name/password → both accounts become active.
- **Lease lifecycle**: draft → active → terminated (with required reason). Activation requires no overlap for room *and* tenant (checked inside a room-locked transaction), sets room `occupied`, and holds the deposit record.
- **Move-out**: settlement form validates `deduction + refund ≤ amount`, writes `Deposit` (held/partially_returned/returned/forfeited), terminates the lease, and frees the room back to `available`.
- **Portal tenant**: dashboard shows room, lease, days remaining and deposit; `/tenant/lease` lists active + history. Queries are always scoped to the authenticated tenant.
- **Seeded demo**: tenant `tenant@kostmanagement.test` is active with an occupied room and held deposit.

## Billing (Phase 4A)

Data model: `invoices → invoice_items`. Invoice columns `subtotal/discount/late_fee/adjustment/total/amount_paid/amount_due` are **cached sums** recomputed by `BillingService::recalculate()` from line items (single source of truth).

- **Calculation services** (pure, testable): `BillingService`, `LateFeeCalculator` (fixed-daily or percentage in **basis points**, `500 = 5.00%`), `ProrationCalculator` (configurable via settings), `InvoiceNumberGenerator` (`INV-{year}-{seq}`).
- **Idempotency**: recurring invoices carry `billing_period` with a DB `unique(lease_id, billing_period)`; manual invoices use `billing_period = NULL` (multiple allowed).
- **Lifecycle**: draft → issued (dispatches `InvoiceIssued` → `InvoiceIssuedNotification` mail + database) → void (row kept, reason required). Paid/partially-paid arrives with Phase 5.
- **Admin UI**: invoice list (filters + billed/outstanding/overdue summary), manual create with dynamic items, detail with item add/remove, issue, void.
- **Tenant portal**: `/tenant/invoices` + detail (own only), dashboard "Tagihan Aktif" widget, bottom-nav **Tagihan**.
- **Seeded demo**: an issued invoice for the sample active lease.

## Billing (Phase 4B)

- **`billing:generate-invoices`** (`--period`, `--lease`): dispatches/ runs `GenerateInvoiceJob` per eligible lease (active/expiring, within the period). Job creates the invoice and issues it; `unique(lease_id, billing_period)` + `firstOrCreate` semantics make re-runs safe.
- **`billing:mark-overdue`**: flips unpaid invoices past `due_date` to `overdue`, records audit, dispatches `InvoiceOverdue` → `InvoiceOverdueNotification` (mail + database).
- **`billing:apply-late-fees`**: deterministically recalculates the late-fee line item per invoice (recomputed, never accumulating) using `LateFeeCalculator`.
- Scheduled in `routes/console.php`: generate on the 1st of the month, mark-overdue and apply-late-fees daily.

## Payment (Phase 5A)

Data model: `payment_methods` (reference data) and `payments` (proof on the **private disk**).

- **Tenant submits** a payment (choose invoice/method, amount, proof) → stays `pending`; the **invoice is never changed** until verification.
- **Admin verifies** → `PaymentService::recalculateInvoice()` derives `amount_paid` from verified payments, sets `amount_due`, and flips the invoice to `paid` / `partially_paid`. **Reject** leaves the invoice untouched; **refund** (verified payment) reverses the calculation; pending payments can be **cancelled**.
- Proof is required for non-cash methods; **cash** is recorded directly by admin as already verified (`recordManual`).
- Overpayment is allowed (amount due clamps to 0, the excess is visible as "overpaid").
- Every financial change writes an `audit_logs` entry; no payment is ever hard-deleted.
- **Admin UI**: payment list (filters + pending/verified totals), detail (view proof, verify/reject/refund), and payment-method management.
- **Tenant portal**: `/tenant/payments` + `/tenant/payments/create` (bottom-nav **Bayar**); proof download is authorized per-tenant.
- **Seeded demo**: 4 payment methods (always) + a pending payment on the sample invoice (local).

### Scheduler

The billing jobs run from Laravel's scheduler. On the Laragon dev box this needs `php artisan schedule:run` every minute (Task Scheduler):

```powershell
schtasks /create /tn "KostManagement Scheduler" /tr "powershell -NoProfile -Command cd C:\laragon\www\KostManagement; php artisan schedule:run" /sc minute /mo 1
```

## Key Decisions

- **Single-owner, multi-property ready** — every business entity carries `property_id` from Phase 2 onward, so a second property does not require a rewrite.
- **Private vs. public storage** — `private` disk for KTP/contract/proof files (served only via authorized endpoint), `public` disk for room photos/logo. No private path is ever exposed.
- **Append-only audit log** — `AuditLog` refuses `update()`/`delete()`; all important records write an `audit_logs` entry via `AuditService`.
- **Settings as a database-backed, cached service** — everything that was formerly a magic string or hard-coded constant (invoice prefix, due days, late-fee formula, reminder schedule) is a `settings` row read through `SettingsService`.
- **Money as integers** — `app\Support\Money` formats `int $rupiah` values with `Rp` and `id_ID` conventions; arithmetic never touches floating point.

## Testing

```bash
php artisan test          # all tests
php artisan test --compact  # the usual local run
```

Initial coverage covers login/registration, profile, route access by role, permission scoping, settings casting, and audit append-only semantics. The duplicate-billing test and full financial tests land with Phases 4 and 5.

## Queue, Scheduler, Storage

### Queue

```env
QUEUE_CONNECTION=database
```

```bash
php artisan queue:listen --tries=1
# or: composer -- dev (runs queue:listen alongside serve + pail + vite)
```

Heavy work (emails, PDF export, image processing) will run on the queue from Phase 4 onward.

### Scheduler

The monthly invoicing scheduler, overdue checks, SLA, and reminders (all idempotent + `Settings`-configurable) land from Phase 4; the `Schedule::` wiring will be documented here as each job ships.

### Storage

```bash
php artisan storage:link   # links public/storage → storage/app/public
```

## Environment

| Key | Default | Notes |
|---|---|---|
| `APP_TIMEZONE` | `Asia/Jakarta` | also in `config/app.php` & `config/kost.php` |
| `APP_LOCALE` / `APP_FAKER_LOCALE` | `id` / `id_ID` | month names, faker locale |
| `FILESYSTEM_DISK` | `local` | default `private`; `configs/filesystems.php` also exposes `private:+public` |
| `KOST_DEV_PASSWORD` | `password` | overrides the password used by `DevUserSeeder` |

## Development Notes

- `bootstrap/app.php` installs `SetRequestId` on `web` (request tracing) and `role:` / `active` middleware aliases.
- `AppServiceProvider` enables `Model::shouldBeStrict()` in local env, installs `Gate::before` for owners, `Password::defaults`, and rate-limited `login`.
- Build output lives in `public/build` (generated by `vite build`). No step should commit it.
- `php artisan pint` is the local formatter.

## Deployment Notes

Detailed notes ship with Phase 10. Checklist at that point will cover `APP_KEY` + cache config, queue worker, scheduler `crontab`, storage permissions, DB backups, logging, HTTPS, and a security review.

## Contributing

Fixes and additional phases are welcome. Enforce `php artisan pint` and `php artisan test` before pushing.
KOST_DEV_PASSWORD is intentionally not committed.
