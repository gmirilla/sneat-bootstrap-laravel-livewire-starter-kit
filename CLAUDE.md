# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MySalam Online — a Laravel 12 insurance agent/broker sales and policy-management platform for Salam Takaful Insurance. It sells motor, fire, and other products, submits policies to NIIP (national insurance registry), and integrates with an internal "Elite" ERP system via a proxy service.

## Commands

**Start everything (server + queue + logs + Vite HMR):**
```bash
composer dev
```

**Individual processes:**
```bash
php artisan serve
php artisan queue:listen --tries=1   # required for NIIP and ECMR jobs
npm run dev                          # Vite HMR
npm run build                        # production assets
```

**Migrations:**
```bash
php artisan migrate
php artisan migrate --path=database/migrations/<specific_file>.php
```

**Cache:**
```bash
php artisan config:clear   # required after .env changes
php artisan cache:clear
```

**Tests (Pest):**
```bash
./vendor/bin/pest
./vendor/bin/pest tests/Feature/ExampleTest.php
```

**Lint:**
```bash
./vendor/bin/pint
```

---

## User Roles

The `users.role` column drives every access-control branch in the app. Roles are:

| Role | Description |
|------|-------------|
| `superadmin` | Full access including broker admin and Elite management |
| `admin` | Policy management, user management, claim queue |
| `agent` | Creates and manages own policies; has a credit quota |
| `subagent` | Sub-account under an agent; borrows from parent's credit pool |
| `user` | End-customer (limited access) |
| `broker` | External broker — sees only the broker portal (see below) |

`DashboardController` and `PolicyController` branch extensively on `$user->role`. `broker` role users are detected early and redirected to broker-specific views with data from Elite API rather than the local DB.

---

## Architecture

### ProxyClient (singleton service)

All calls to the Elite ERP and ECMR services go through `App\Services\ProxyClient`. It is registered as a singleton in `AppServiceProvider` using `PROXY_URL` and `PROXY_SECRET` from `config/variables.php`.

**Critical OS difference**: On Windows (local dev) it uses Laravel's `Http::` client. On Linux (production) it uses the `curl` binary via `shell_exec` to bypass PHP's stale CA bundle. Both send `X-Proxy-Secret` header for auth.

Always call `$proxy->isConfigured()` before use; return early if false.

### Config / Environment variables

All env vars are centralised in `config/variables.php` (not scattered across config files). Use `config('variables.KEY')` to access them.

**Gotcha**: If `.env` contains `KEY=` (empty value), `env()` returns `null` — the default value in `env('KEY', 'default')` is only used when the key is *absent* from `.env`. Always guard with `config('variables.KEY') ?: null` before passing to `Mail::to()` or similar.

### Broker Portal

Brokers are linked to the Elite ERP via `users.broker_id` (integer, set by admin when creating the account). The three broker controllers share a `fetchPolicies(int $brokerId): array` pattern:

```php
$cacheKey = "elite_policies_{$brokerId}";
$cached   = Cache::get($cacheKey);
if ($cached !== null) return $cached;
// ... proxy call ...
if (!empty($data)) Cache::put($cacheKey, $data, 900);  // only cache non-empty
return $data;
```

**Never use `Cache::remember` for broker portfolio data.** `Cache::remember` caches empty arrays when the proxy is down, and then serves stale empties for 15 minutes after the proxy recovers. Always use `Cache::get` + conditional `Cache::put`.

The shared cache key `elite_policies_{broker_id}` is used by `BrokerPolicyController`, `BrokerTicketController`, `BrokerClaimNotificationController`, and `DashboardController`. A single `Cache::forget("elite_policies_{$brokerId}")` clears all of them.

### Policy numbers in routes

Elite policy numbers contain forward-slashes (e.g., `SLM/MTR/2025/00123`). Any route with a policy number segment must include:

```php
->where('policyNo', '.+')
```

Without this, Laravel treats the slashes as path separators and returns 404.

### Policy lifecycle (agents/subagents)

1. Agent submits motor policy → `PolicyController::submitmpolicy()` → policy saved locally
2. On confirmation/payment → `PostNIIPDataSlow` job dispatched (queued, non-blocking)
3. `PostNIIPDataSlow` posts to NIIP API; on success → dispatches `CheckEcmrJob`
4. `CheckEcmrJob` checks ECMR (vehicle registration status) via proxy — only runs for motor policies and skips if a non-failed check exists within the last year

The queue worker (`php artisan queue:listen --tries=1`) must be running for NIIP/ECMR to work.

### Claim notifications

There are two separate flows for claim notifications:

- **Public flow** (`ClaimNotificationController`): 3-step session-based PRG (lookup → form → confirmation). Policy lookup accepts email *or* phone (either is sufficient). Sends `ClaimNotificationMail` to `CLAIMS_EMAIL`.
- **Broker flow** (`BrokerClaimNotificationController`): skips lookup; broker selects from their Elite portfolio. `policy_source = 'elite_broker'` distinguishes these records. `is_third_party` boolean controls whether claimant details come from the broker form or from the insured name on the policy.

`ClaimNotificationMail` sets `replyTo` from `claimant_email`, which can be null for broker own-damage claims. Use `array_filter([$notification->claimant_email])` to produce an empty array rather than `[null]`.

Reference numbers use pattern `CLN-YYYYMMDD-RANDOM6` (collision-checked against DB).

### Agent credit system

`agentsdetailsModel` tracks credit per product type (`private_allocated`/`private_used`, `commercial_allocated`/`commercial_used`) plus pool quotas for agents with subagents. The total `noallocated`/`noused` fields are kept in sync as sums. When a subagent issues a policy, credit is drawn from the parent's pool if the subagent's direct allocation is exhausted.

### New broker account flow

1. Admin looks up broker in Elite via `EliteBrokerController` (`/elite/brokers`)
2. Admin creates a portal account for the broker — this sets `users.role = 'broker'` and `users.broker_id = <elite_id>`, with `account_status = 'pending'`
3. Admin approves the account (`UserController::approveAccount`) — sets `account_status = 'active'` and triggers a Laravel password-reset email so the broker can set their own password

---

## Frontend

- **Theme**: Sneat Bootstrap 5 — brand colours `#161616` (dark/black) and `#B18752` (gold). Primary action buttons use `style="background:#161616;color:#B18752;font-weight:700;"`.
- **No frontend framework**: plain Blade + Bootstrap 5. Livewire Volt is only used for the auth settings pages (`resources/views/livewire/`).
- **Layouts**: `<x-layouts.app>` for authenticated pages. Menu components are in `resources/views/components/layouts/menu/` (both `horizontal.blade.php` and `vertical.blade.php` must be updated when adding menu items).
- **Pagination**: Bootstrap 5 style, configured globally in `AppServiceProvider::boot()`.
- **Charts**: Chart.js 4.4.4 loaded from CDN inline on the dashboard — used for broker doughnut (portfolio mix) and bar (6-month expiry) charts.

---

## Key Environment Variables

| Variable | Purpose |
|----------|---------|
| `PROXY_URL` | Base URL of the Elite/ECMR proxy server |
| `PROXY_SECRET` | Shared secret sent as `X-Proxy-Secret` header |
| `CLAIMS_EMAIL` | Recipient for claim notification emails (must not be blank) |
| `TECHNICAL_EMAIL` | Recipient for technical support emails |
| `NIIP_URL` | NIIP motor insurance registry API endpoint |
| `NIIP_API_KEY` | NIIP API key |
| `API_ELITE_URL` | Elite ERP API base URL |
| `API_ELITE_TOKEN` | Default Elite agent token |

---

## Elite Broker API Reference

See `elite-broker-apis.md` for request/response shapes. The two endpoints used are:

- `GET /api/elite/brokers` — list all broker/agent partners
- `GET /api/elite/broker/policies?broker_id=N` — portfolio for a specific broker

Both require `X-Proxy-Secret` (sent automatically by `ProxyClient`).
