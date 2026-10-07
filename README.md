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
| **3 — Tenant & Lease** | tenants, documents, invitation flow, leases, move-in/out, deposit settlement | 🔜 Next |
| **4 — Billing** | invoices, invoice items, monthly generation + idempotency, proration, late fee | Planned |
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
