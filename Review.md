# MySalam Online — Code Review & Hardening Tracker

This is a living document. Work through the findings by priority, and update each item's **Status** and the **Change Log** at the bottom as fixes land. Do not delete resolved items; mark them `FIXED` with the commit/date so there is an audit trail.

- **Review #1:** 2026-11-02, branch `Salam-Online-Sale-Prod`, commit `47d7e70`
- **Method:** Manual read of routes, controllers, services, jobs, the `User` model and config. Views, migrations, the Excel imports and the remaining small CRUD controllers were skimmed or not reviewed (see [Coverage](#coverage--not-yet-reviewed)).
- **Status values:** `OPEN` · `IN PROGRESS` · `FIXED` · `WONTFIX` · `NEEDS VERIFY`
- **Severity:** Critical (exploitable now, financial or data impact) · High · Medium · Low

---

## Summary

The largest risk is **missing authorization**. Almost every route is guarded only by `auth`, and role checks exist in a handful of controllers (`AdminJobController`, `AdminBrokerTicketController`, parts of `UserController`, `ClaimNotificationController`). Many privileged actions have **no role check at all**. Public self-registration is open and creates an active `user`-role account, so **anyone on the internet can register and then reach those endpoints**. The most serious chain is:

> Register → `POST /agent_update` (grant yourself credit and set your own Elite `auth_token`) or `POST /update_user` (set your own role to `superadmin`) → issue policies or cancel policies.

The second theme is **payment and credit integrity**: policies can be issued on someone else's payment record, credit is deducted after the external call without locking, and a half-written payment service is wired to a route that does not exist.

| Severity | Count |
|----------|-------|
| Critical | 8 |
| High     | 9 |
| Medium   | 12 |
| Low / Code quality | 12 |

**Progress (Fix #2, 2026-11-02):** High items H-1 to H-9 worked through (see each item; H-2, H-4 and H-6 essentially done; H-1, H-3, H-5, H-7, H-8 and H-9 partial with open decisions listed). Medium and Low items untouched.

**Progress (Fix #1, 2026-11-02):** Critical items C-1…C-8 are addressed in the working tree (uncommitted); C-4 is partial pending a business decision on public registration. High/Medium/Low items are untouched.

**Recommended order of attack:** C-1 through C-3 first (a single middleware plus a policy layer closes most of the Critical items), then C-4 to C-8, then High, then the rest.

---

## Critical

### C-1 Privilege escalation: any logged-in user can change any user's role
- **Where:** `UserController::updateuser` (`app/Http/Controllers/UserController.php:44`), route `POST /update_user` (`routes/web.php`, `auth` only)
- **Problem:** No role check. `uid` and `role` come straight from the request, with no validation and no allow-list. Any authenticated user (including a freshly self-registered one) can POST `uid=<self>&role=superadmin`. `$user` is also not null-checked.
- **Side effects:** Changing a user to a non-agent role deactivates their agent record; setting role to `agent` for an existing record sets status to `deactivated` (inverted logic, looks like a bug).
- **Fix:**
  1. Add a role-gating middleware, e.g. `role:admin,superadmin`, and apply it to all admin route groups.
  2. Validate `role` with `Rule::in([...])` and `uid` with `exists:users,id`.
  3. Only `superadmin` may grant `admin`/`superadmin`.
  4. Log role changes (who, whom, from, to).
  5. Fix the activated/deactivated logic.
- **Status:** **FIXED (2026-11-02, uncommitted).** `role:admin,superadmin` middleware on route; `updateuser` now validates `uid`/`role` (`Rule::in`), only a superadmin may grant/alter admin roles, self role-change blocked, role changes logged, agent activate/deactivate logic corrected (re-promotion now sets `activated`). Tests: `AuthorizationTest`.

### C-2 Anyone can grant themselves agent credit and set Elite auth tokens
- **Where:** `AgentsdetailsModelController::agentupdate` (`:60`), route `POST /agent_update`. Also `index`, `agentprofile` (`list_agents`, `agent_profile`).
- **Problem:** No role check. A normal user can create or update an `agentsdetailsModel` row for any `userid`, setting `private_allocated`, `commercial_allocated`, `allowcredit`, `status`, `auth_token`, `canregistersubagent`. That is free policy issuance on agency credit. `list_agents` and `agent_profile` also expose every agent's `auth_token` (Elite credential) to any authenticated user. The `try/catch (\Throwable)` swallows all errors silently.
- **Fix:** Admin-only middleware; validate inputs (`integer|min:0`, `in:` for status); remove the empty catch; never render `auth_token` in lists (mask it); audit-log credit changes.
- **Status:** **FIXED (2026-11-02, uncommitted).** Agent admin routes are `role:superadmin`; `agentupdate` validates all inputs, no longer swallows errors, and logs before/after values. *Still open from this item:* `auth_token` is still rendered in the superadmin pages and stored in plaintext (tracked under H-5).

### C-3 Other admin-only endpoints have no authorization
All reachable by any authenticated account, including `user` and `broker` roles:

| Route | Controller | Impact |
|-------|-----------|--------|
| `GET /generate-api-token?auid=` | `UserController::generateeapitoken` | Mints a **Sanctum API token for any user id** (including admins) and returns it as JSON. Account takeover of the API channel. `$user` not null-checked. Also a state-changing GET. |
| `POST /subagent/{id}/reset-password` | `UserController::resetPassword` | **Reset any user's password** (route-bound `User $id`), including superadmin. Full account takeover. Min length 6 / max 16 is also a weak policy. |
| `GET /list_users` | `UserController::index` | Lists all users (PII). |
| `POST elite/brokers/create-account`, `GET elite/brokers`, `GET elite/broker/policies` | `EliteBrokerController` | Comments say "admin only" but there is **no check**. Anyone can create broker accounts (`account_status` is hard-coded `active`, contradicting the documented `pending`→approve flow) and read any broker's Elite portfolio via `?broker_id=`. |
| `GET niip_code_mgmt`, `GET /update-states`, `/update-colors`, `/update-lgas`, `/update-vmake`, `POST import_*`, `POST /niip-manual-import/import` | various | Reference-data imports and NIIP re-posting open to every user. `niip-manual-import` lets any user post arbitrary spreadsheet rows to NIIP with the company API key. |
| `GET retry_niip?policyno=` | `PolicyController::retryniip` | No role check and no ownership check; `$policy` is not null-checked (500). |
| `GET test_async` | `PolicyController::testasync` | Test endpoint dispatching a job with a hard-coded policy number. **Remove from production.** |
| `GET ecmr_index`, `GET emcr_check` | `EcmrController` | `ecmr::all()` exposed to all users; the check triggers a proxy call. |
| `POST claim_check`, `claim/send_enquiry` | public | `claim_check` is unthrottled (see M-3). |

- **Fix:** Introduce `EnsureRole` middleware (or Gates/Policies) and apply it per route group. Prefer deny-by-default: a single group `Route::middleware(['auth','role:admin,superadmin'])` for all admin routes. Replace the duplicated `requireAdmin()`/`authorize()` private helpers (note `AdminJobController::authorize` shadows the framework's `AuthorizesRequests::authorize`).
- **Status:** **FIXED (2026-11-02, uncommitted).** Role gates added: `elite/*` → superadmin; `niip_code_mgmt`, all `list_*`/`import_*`/`update-*` reference-data routes and `niip-manual` → superadmin; `ecmr_index`/`emcr_check`/`retry_niip` → admin; `generate-api-token` → superadmin (validated target, logged); sub-agent password reset restricted to the parent agent or an admin (never to admin accounts by an agent) with 8-char minimum; `test_async` route and method removed; `retryniip` null-checked. Form look-up endpoints (`get-colors`, `get-lga`, `get-vehicle-models`) stay `auth`-only. *Not changed:* `generate-api-token` is still a GET (UI uses `fetch`), `claim_check` is still unthrottled (M-3), and `createBrokerAccount` still creates accounts as `active` rather than `pending` (doc drift, L-11).

### C-4 Open self-registration creates an active, API-token-bearing account
- **Where:** `resources/views/livewire/auth/register.blade.php:22-38`
- **Problem:** Public registration is on, `account_status` defaults to `active` (migration default), and an API token is created and discarded (`$token` unused). Combined with C-1…C-3 this is the entry point of the escalation chain. `Registered` fires, but `MustVerifyEmail` is commented out in `User`, so email is never verified.
- **Fix:** Disable public registration unless it is a business requirement. If needed: enable `MustVerifyEmail`, add the `verified` middleware to sensitive routes, create accounts as `pending` pending approval (the claim flow already does this), remove the unused token creation, and throttle registration.
- **Status:** **PARTIALLY FIXED (2026-11-02, uncommitted).** The unused API token created at registration was removed, and with C-1…C-3 closed a self-registered `user` can no longer escalate. *Open:* public registration itself is unchanged (accounts are `active`, email unverified) — this is a **business decision** (customers may buy direct). If it should stay, enable `MustVerifyEmail`, add `throttle` on register, and consider `pending` + approval.

### C-5 `paypolicy` — no ownership check, policy can be issued against anyone's record
- **Where:** `PolicyController::paypolicy` (`:461`), `POST policy/pay_policy`
- **Problems:**
  1. `policyid` is taken from the request and never checked against `Auth::id()` / role. Any user can trigger issuance (and NIIP submission) of any draft policy, charging their own credit or Paystack payment to it. Combined with the agent-credit bypass in C-2 this is fraud-enabling.
  2. `$policy`, `$insured`, `$policyrisk` are not null-checked, so a bad id gives a 500 with stack trace if `APP_DEBUG=true`.
  3. **No state guard.** A policy already `approved` can be re-submitted: a double click or a replay issues a second Elite policy and deducts credit twice. Require `status === 'draft'` (or `failed`) and wrap the transition in a DB transaction with `lockForUpdate()` on the policy row.
  4. **Paystack verification (`:516-545`):** `$transaction` is not null-checked (`->where(...)->first()` then property access, so a forged reference gives a 500). The amount comparison is a loose `==` on a float. The client controls `$request->paystack` (JSON decoded with no validation). Reference reuse is not prevented: a single successful reference can be replayed against many policies **as long as a `paystacktransaction` row exists for that policy**, and the Paystack currency is not checked. Also check `reference` is unused (unique constraint on `reference_code` where status is success) and that the verified transaction's email/metadata matches.
  5. When neither `agencycredit` nor `paystack` is present the `default:` branch falls through and **issues the policy with no payment at all** (`:547-549`, `default: break`), using `$token` undefined. This is a free-policy bug.
  6. A non-agent/non-subagent user who sends `agencycredit` gets `$creditleft` undefined → `?? 0` → blocked (OK), but an `admin`/`superadmin` with `agencycredit` also falls to "insufficient" with no explicit handling.
- **Fix:** Authorize (policy owner or admin), enforce state machine, make the `default` branch an error, null-check everything, use `hash_equals`/integer kobo comparison, and make each payment reference single-use.
- **Status:** **FIXED (2026-11-02, uncommitted).** `paypolicy` now: validates `policyid`; takes a per-policy `Cache::lock`; authorizes via `PolicyPolicy` (view + update ⇒ owner/admin and `draft`/`failed` only, so approved policies cannot be re-issued); null-checks risk/insured; requires an explicit payment method (**no more fall-through issuing without payment**); Paystack branch validates the JSON reference, requires a transaction row for this policy, rejects references recorded against any other row, compares integer kobo + currency, and no longer dereferences a null transaction. `PAYSTACK_*` moved to `config('variables')` (partly H-5). *Not covered:* a payment reference is still bound only through the `paystacktransactions` table — add a DB unique index on `reference_code` (migration not written).

### C-6 Credit deduction is non-atomic and happens after the external call
- **Where:** `PolicyController::paypolicy` (`:600-680`), `agentupdate`, `registerSubAgent`, `subAgentCredit*`, `poolUpdate`
- **Problem:** Available credit is read, the Elite call is made (up to 3 retries × 30 s), and only then `$agent->private_used += 1; save()` runs. Two parallel requests (or double-click) both pass the "credit left > 0" check and both issue policies; the read-modify-write on `private_used` also loses updates (`+=` on an in-memory value rather than `increment()`). The pool branch uses `$parentagent->lockForUpdate()->increment(...)` **inside a transaction after the check was done outside it**, so the lock does not protect the check. The credit sync (`syncTotals`) is a second non-atomic write.
- **Impact:** Over-issuance beyond purchased credit (direct financial loss), and counter drift that is hard to reconcile.
- **Fix:** Reserve credit *before* calling Elite inside a DB transaction with `lockForUpdate()` on the agent row (check + increment together), then release/refund on Elite failure. Alternatively use `increment()` with a `WHERE used < allocated` guard and check affected rows. Add a `credit_ledger` table (agent, policy, delta, reason, actor) so balances can be audited and reconciled.
- **Status:** **FIXED for the issuance path (2026-11-02, uncommitted).** `reserveCredit()` checks and increments inside a DB transaction with `lockForUpdate()` **before** the Elite call; `releaseCredit()` gives it back on timeout, Elite failure or any exception; it also now enforces `allowcredit` and `status !== deactivated` server-side. `registerSubAgent` check + allocation is also done under a row lock. *Still open:* `subAgentCreditAdd/Remove`, `poolUpdate` and `cancelpolicy` refunds still use read-modify-write; no `credit_ledger` yet. Note `lockForUpdate()` is a no-op on SQLite (fine for tests; production DB must be MySQL/Postgres).

### C-7 `viewpolicy` / `init_paystack` / `policy.confirm` — insecure direct object references
- **Where:** `PolicyController::viewpolicy` (`:686`), `init_paystack` (`:428`), `PolicyPaymentController::confirm`, `renewpolicy`, `PolicyController::submitmpolicy` (policyid edit path, `:299`)
- **Problem:** Policies are loaded by id from the request with no check that the current user owns the policy (agent_id / insured_id / parent agent) or is an admin. Any logged-in user can read any policy and its insured's PII, and can initiate Paystack transactions for any policy id. In `submitmpolicy`, `policy::find($request->policyid)` allows **overwriting any other agent's policy** (including approved ones) back to `draft` with new details.
- **Fix:** Add a `PolicyPolicy` (Laravel Policy) with `view`, `update`, `pay`, `cancel`; use route-model binding + `$this->authorize()`. Reject edits unless `status` is `draft`/`failed`.
- **Status:** **FIXED (2026-11-02, uncommitted).** New `PolicyPolicy` (`view`: admin, creator, insured, or the creator's parent agent; `update`: same + `draft`/`failed`), registered in `AppServiceProvider`. Enforced in `viewpolicy`, `init_paystack`, `PolicyPaymentController::confirm`, the edit path of `submitmpolicy` and `paypolicy`. *Not covered:* `renewpolicy` is an empty stub (no data access); `list_policy_subagents` and reporting endpoints were not re-audited.

### C-8 `registerSubAgent` — acts on a user chosen by the URL, not the caller
- **Where:** `AgentsdetailsModelController::registerSubAgent(User $agent, ...)`, route `POST sub_agents/register_new/{agent}`
- **Problem:** The permission check uses the *route's* `$agent` instead of `Auth::user()`. Any user can register sub-agents under any agent who has `canregistersubagent`, spending **that agent's credits** and creating login accounts. Also: `adress` (typo) is not a column or fillable (address is silently dropped); `parentid` is set via mass assignment; `password` is only 6 chars; no check that the caller's role is `agent`.
- **Fix:** Use `Auth::user()`, require `role === 'agent'`, wrap user + details + parent credit update in one transaction, fix the `address` key, raise password policy.
- **Status:** **FIXED (2026-11-02, uncommitted).** `registerSubAgent` aborts 403 unless the route `{agent}` is the logged-in `agent`; `address` key typo fixed (`adress`); password minimum raised to 8; parent credit check + sub-agent creation + reservation now run in one locked transaction. `subagentsprofile` is limited to the parent or an admin.

---

## High

### H-1 Third-party API (`POST /api/third-party`)
- **Where:** `app/Http/Controllers/Api/ThirdPartyController.php`
- **Problems:**
  - New insured accounts are created with the **hard-coded password `'Password'`** (also `PolicyController::submitmpolicy:278`). Anyone who knows or guesses a customer's email/phone can log in. Use `Str::random(32)` and send a reset/setup link, or create the account without login capability.
  - **No credit check and no payment** — it issues policies on Elite using the agent's token with no deduction from `agentsdetailsModel`. Verify this is intentional; if not it is a free-issuance channel.
  - `$agent = agentsdetailsModel::where(...)->first(); $agent->auth_token` — null dereference when the caller has no agent record (500). Callers are not checked for role.
  - `address` is not validated (`$request->address` used raw); `$vmake`/`$vmodel` null-dereferenced if not matching; `default:` branch uses `back()->with()` in a JSON API.
  - Contributions (15000/20000/5000) are hard-coded, duplicated in two switch blocks, and the later `if` chain mutates `$policy` **after** `save()` without saving again (so `insurancetype` / `vehicleuse` mutations are lost).
  - The entire request body (PII) is appended to `storage/logs/api_log.txt` with `file_put_contents`, unrotated and un-redacted, and uses `$_SERVER` directly. Use the `Log` facade with redaction.
  - `certificate_url` uses plain `http://` to the Elite host.
  - No idempotency: a client retry creates duplicate policies. Add an idempotency key.
  - Sanctum tokens never expire (check `config/sanctum.php` `expiration`), and there is no token ability scoping or throttle on `/api/*`.
- **Status:** **PARTIALLY FIXED (2026-11-02, uncommitted).** Done: caller must be an active `agent`/`subagent` with an agent record (403 otherwise; no more null-dereference); new insureds get `Str::random(40)` instead of `'Password'`; request-body dump to `api_log.txt` replaced by a PII-free `Log::info`; unknown make/model/colour return 422 instead of a 500; JSON error (not `back()`) for an unknown product; `address`/`phone` validated. *Correction to the original finding:* the in-memory `insurancetype`/`vehicleuse` changes are persisted by the later `save()` on success, so they are not lost. *Open - needs a decision:* the API still issues **without deducting credit or taking payment** (confirm this is intended for integrators); no idempotency key; Sanctum tokens never expire (`config/sanctum.php`); no `throttle` on `/api/*`; contribution amounts still hard-coded; `certificate_url` is `http://`.

### H-2 Hard-coded default password `'Password'` for agent-created insureds
- **Where:** `PolicyController::submitmpolicy:278` (and H-1). Same remediation. Also, matching existing insureds **by phone number only** (`User::where('telno', ...)->first()`) means an agent can attach a policy to any existing user (including staff) by typing their phone, and the existing record's details are silently reused/not updated.
- **Status:** **FIXED (2026-11-02, uncommitted).** `submitmpolicy` uses an unguessable password and only reuses an existing account by phone when it is a `role = 'user'` account (an agent can no longer attach a policy to staff/broker accounts). **Clean-up of existing data:** new command `php artisan users:reset-default-passwords --dry-run` lists accounts still using `Password`; run without `--dry-run` to reset them (owners use "forgot password"). Run it once in production (a dry run on the local database found 0).

### H-3 Unauthenticated information disclosure and abuse surface
- **Brown card lookup** (`/browncard`): searching by registration number returns policyholder name, vehicle and policy number to anonymous users and also queries NIIP/NIID with company credentials, 5/min per IP only. Lookups by plate enumerate policyholders. Consider showing only masked holder name, adding CAPTCHA, and keeping the PII behind the existing signed download.
- **Claim lookup** (`claim/notify`): policy number + *either* email or phone is enough to retrieve policy details and open a claim. Phone numbers and emails are low-entropy secrets. Add rate limiting per policy number (not just IP) and consider an OTP to the registered contact.
- `GET /ninverification` is public and unthrottled (view only; confirm it has no backend).
- **Status:** **PARTIALLY FIXED (2026-11-02, uncommitted).** Brown-card result now shows a masked holder name (`A***** O******`); claim lookup has a per-policy-number limiter (10/hour) on top of the per-IP route throttle. *Open:* CAPTCHA / OTP to the registered contact for claim lookup, whether plate-based brown-card lookup should return holder/vehicle details at all, and `GET /ninverification` (public view - confirm no backend).

### H-4 `ProxyClient` shell execution on Linux
- **Where:** `app/Services/ProxyClient.php:44-48, 75-80`
- **Problem:** Uses `shell_exec` + `curl`. Arguments are `escapeshellarg`-ed (good), but: the **proxy secret appears on the process command line** (visible via `ps` to other local users), the HTTP status is never checked so error pages come back as "successful" bodies, `-s` hides errors, and only `GET`/`POST` are supported (any other method silently becomes GET). The Windows/Linux behavioural split means dev never tests the prod path.
- **Fix:** Use Laravel's `Http` client on both platforms and fix the CA bundle (`curl.cainfo` / `openssl.cafile` in `php.ini`, or update the `ca-certificates` package). If curl must stay, pass headers via `-H @file`/stdin config (`curl -K -`) rather than argv, and check `-w '%{http_code}'`.
- **Status:** **FIXED (2026-11-02, uncommitted).** `ProxyClient` no longer uses `shell_exec`: curl is run via `proc_open` (no shell) with the URL, `X-Proxy-Secret` header and JSON body supplied as a curl config on **stdin**, so the secret is not visible in `ps`. The HTTP status is read back and logged, non-GET/POST methods throw, and both OS paths share one `request()` entry point. Verified manually against a local server with `curl.exe` (quotes/backslashes/unicode in the secret and body round-trip; a 422 body is returned; a dead host returns `null`). *Not changed:* the stale-CA-bundle root cause; `curl` must be on the PATH of the PHP user in production; the Linux path is not exercised by the test suite.

### H-5 Secrets and configuration
- `env()` is called directly in controllers (`PAYSTACK_SECRET_KEY`, `PAYSTACK_ELITE_TOKEN`) — returns `null` once `php artisan config:cache` is used in production, silently breaking payments. Move to `config/services.php` (a half-built `PaystackService` already references `config('services.paystack.secret')`, which probably does not exist). Contradicts the project convention in `CLAUDE.md`.
- `.env.example` defaults to `APP_DEBUG=true`, `APP_ENV=local`. Make sure production overrides these; stack traces with request data would leak otherwise.
- `SESSION_ENCRYPT=false`, sessions in DB. Consider `true`, plus `SESSION_SECURE_COOKIE=true` and `SESSION_SAME_SITE=lax|strict` for production.
- Agents' Elite `auth_token`s are stored **in plaintext** in `agentsdetails_models.auth_token`. Cast with `encrypted`.
- `config/variables.php` still carries unused template boilerplate (ThemeSelection URLs, a signed S3 `ogImage` URL with an embedded access key ID and signature). Remove it.
- **Status:** **PARTIALLY FIXED (2026-11-02, uncommitted).** Done: `PAYSTACK_SECRET_KEY`/`PAYSTACK_ELITE_TOKEN` read from `config('variables')` (no `env()` in controllers); `agentsdetailsModel.auth_token` is `encrypted` at rest via a new migration (`2026_11_02_000001_encrypt_agent_auth_tokens`, idempotent, widens the column) and is no longer echoed to the admin form (blank keeps, checkbox clears) or selected in the agent list; the expired signed S3 `ogImage` URL (with embedded key id) was blanked in `config/variables.php`; `.env.example` now defaults to `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_ENCRYPT=true` and lists the integration keys. **Deploy notes:** run `php artisan migrate` together with this code (plaintext tokens would otherwise fail to decrypt), and back up `APP_KEY` - rotating it makes stored tokens unreadable. *Open:* production `.env` must be checked (`APP_DEBUG`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE`); `users.apitoken` still plaintext (M-5); template boilerplate keys remain in `config/variables.php` because the Blade `<head>` still reads some of them.

### H-6 Race / duplicate handling and error-swallowing in the payment pipeline
- `paypolicy` null-derefs on `$transaction` / `$paystackData` (see C-5).
- `create_paystack_transaction` (`PaystacktransactionController`): raw `curl_*` with **no timeout**, no `curl_error` handling, `$paystackresponse->status` dereferenced on `null` if curl fails, `env()` use (H-5), and `$ref_id = uniqid()` (predictable, not unique under concurrency). The `amount` is taken from `Request`, **but the controller is invoked internally with a hand-built `Request`**, so validation still runs only on the policy_id existence. Use `Http::` with timeout/retry and the policy's server-side contribution.
- `init_paystack` is a `GET` that creates DB rows and an external transaction — should be `POST` with CSRF and ownership checks.
- `verify_payment` returns the raw Paystack body to the caller and is also invoked without validating `$transaction` exists.
- **Status:** **FIXED except the HTTP verb (2026-11-02, uncommitted).** `PaystacktransactionController` rewritten on Laravel's `Http` client with timeouts and error handling; references are UUIDs; the charged amount always comes from the policy (not the request); `verify_payment` returns a 404/502 JSON body instead of dereferencing null, so `paypolicy` fails closed. Dead resource stubs removed. *Open:* `init_paystack` is still a state-changing `GET` (the SIP view uses it as a link/AJAX URL) - convert to `POST` together with those views.

### H-7 `PolicyPaymentService` / `PolicyPaymentController` are broken but routed
- `Policy::findOrFail` in `activatePolicy` — the model class is `policy` (lower-case) and `Policy` is not imported: `Class not found` on Linux (case-sensitive autoload). `$policy->activate($user)` does not exist on the model. `route('policy.success')` is not defined. `payWithCredit` has a `// deduct credit` comment and no deduction (**a free-policy path if it were ever wired up**). `paystack_reference` is verified, but the policy id is taken from Paystack `metadata` that the app never sets. The routes `policy/{id}/confirm` and `policy/pay` are live now.
- **Fix:** Either finish and test this refactor or remove the routes/classes until ready. Do not leave a credit-less payment path reachable.
- **Status:** **MITIGATED (2026-11-02, uncommitted).** *Correction:* PHP class names are case-insensitive and `use App\Models\policy` aliases `Policy`, so `Policy::findOrFail` resolves; the real defects were the missing `activate()` method, the undefined `policy.success` route, and the credit path with no deduction. Now: `payWithCredit` refuses outright (no free issuance), `activatePolicy` requires `PolicyPolicy::update` (403 otherwise) and fails with a clear validation message while `activate()` does not exist. **Product decision needed:** the SIP (Salam Investment Plan) views POST `policyid` + a JSON `paystack` field to `pay_policy` -> `PolicyPaymentController::pay`, which expects `paystack_reference`/`agency_credit`; online SIP payment therefore does not work today (it already failed before these changes). Either finish this service or point SIP at `PolicyController::paypolicy`.

### H-8 Excel/CSV import endpoints
- `NiipManualController::uploadExcel` stores uploads under `uploads/` and never deletes them; relies on `mimes:` (extension/MIME based); path built as `storage_path('app/private/'.$path)` (breaks if the disk root changes); the loop POSTs each row synchronously with a 180 s timeout inside a web request (will hit PHP/FPM timeouts and cannot be retried) and has no per-row validation, `$row[3]` undefined-index errors, and logs full PII payloads. Missing admin authorization (C-3). Move to a queued job with row validation and a result report.
- The reference-data import/update endpoints (`updatestates` etc.) call external APIs in a loop of N+1 HTTP requests inside a GET request.
- **Status:** **PARTIALLY FIXED (2026-11-02, uncommitted).** `NiipManualController::uploadExcel` now validates (`file|mimes|max:5120`), stores on the `local` disk and dispatches `ProcessNiipManualImport`, which validates each row (policy no, reg no, date), posts to NIIP with a 180 s timeout per row, logs row numbers/outcomes **without PII payloads**, and always deletes the upload. The controller file was renamed `niipmanualController.php` -> `NiipManualController.php` (PSR-4 on Linux; the routes' `ECMRController` -> `EcmrController` typo was fixed in Fix #1). *Open:* the `update-states/colors/lgas/vmake` endpoints still make N+1 HTTP calls inside a GET request (now superadmin-only); no per-row result report in the UI. Requires the queue worker to be running. **Git note:** with `core.ignorecase=true` (Windows) git does not see the case-only rename; run it in two steps: `git mv app/Http/Controllers/niipmanualController.php app/Http/Controllers/tmp.php && git mv app/Http/Controllers/tmp.php app/Http/Controllers/NiipManualController.php` so the index (and Linux checkouts) get the correct name.

### H-9 No test coverage of the critical paths
- `tests/` contains only scaffold tests (auth, settings, dashboard). Nothing covers credit accounting, payment verification, role gating, broker caching, claim flows or the API. Given C-1…C-8, add feature tests that assert **403 for every role that should not reach each privileged route** — those tests would have caught most of this review.
- **Status:** **PARTIALLY FIXED (2026-11-02, uncommitted).** Added `tests/Feature/AuthorizationTest.php` (role x route matrix, escalation, IDOR, payment guards, credit release) and `tests/Feature/HighItemsTest.php` (API gating, token encryption/non-echo, ProxyClient, masking, throttle, payment route, NIIP import queueing). Suite: 86 pass, 2 pre-existing failures (`DashboardTest` - null agent record in `DashboardController`; `PasswordResetTest` - fails on the untouched baseline too). *Still missing:* concurrent credit use, broker portal, claim flows, NIIP/ECMR jobs.

---

## Medium

| ID | Finding | Where | Recommendation |
|----|---------|-------|----------------|
| M-1 | **Broker portal does not enforce `role === 'broker'`.** Broker routes only require `auth`. A non-broker with `broker_id = null` triggers `TypeError` (`fetchPolicies(int $brokerId)`) → 500; an admin's portfolio view is undefined. | `BrokerPolicyController`, `BrokerTicketController`, `BrokerClaimNotificationController`, routes | Add `role:broker` middleware. |
| M-2 | **Broker ticket creation trusts `policy_no`.** The ticket is not validated against the broker's own portfolio, so a broker can open tickets/claims on another broker's policy number. Verify membership via the cached portfolio (as `show()` does). | `BrokerTicketController::store`, `BrokerClaimNotificationController::store` | Validate against `fetchPolicies($user->broker_id)`. |
| M-3 | **Throttling gaps.** `claim_check` (POST, public) has no throttle; login/register/forgot-password rely on defaults; `/api/*` has none. | `routes/web.php`, `routes/api.php` | Add `throttle:` and a login `RateLimiter`. |
| M-4 | **Mass assignment of `role`, `account_status`, `broker_id`, `parentid`, `apitoken` in `User::$fillable`.** Any future `User::create($request->all())` or `update($request->...)` becomes a privilege escalation. The register component passes only validated keys today. | `app/Models/User.php` | Remove privileged fields from `$fillable`; set them explicitly. |
| M-5 | **`apitoken` column stores the plain-text Sanctum token** (`setApitoken`). Tokens are hashed in `personal_access_tokens`, but a copy is kept in `users.apitoken`, defeating that. | `User::setApitoken` | Show the token once and do not persist it. |
| M-6 | **Attachment handling.** Validation is extension/MIME-based; stored with the original name only in DB (good) but served with `response->download` of the stored path; downloads of tickets/claims exist in three controllers with duplicated, subtly different authorization (`BrokerClaimNotificationController::downloadAttachment` check should be verified). No AV scanning. | `ClaimNotificationController`, `BrokerTicketController`, `BrokerClaimNotificationController` | One shared authorization policy for attachments; consider `image`/`mimetypes` rules and scanning. |
| M-7 | **`ClaimNotificationController::submit` links claims to any existing account by email.** An anonymous claimant who knows an existing user's email attaches the claim (and reply/attachment access) to that user, and a rejected account is silently reset to `pending`. | `:133-156` | Don't reuse existing accounts silently; create the claim by email without a user link until approved, or require verification. |
| M-8 | **N+1 and unbounded queries.** `PolicyController` list branches and `AgentsdetailsModelController::index` (raw `DB::table` join, no pagination); `ecmr::all()`; `states::all()`/`vehiclecolor::all()` on every `viewpolicy`; `listpolicySubagents` uses `->get()` with no pagination; `retryAllFailedNiip` loads all candidate policies then filters in PHP; `csvExport` fine (chunked). | various | Paginate, eager-load, cache reference data. |
| M-9 | **Cache semantics.** Broker portfolio cache is per `broker_id` for 15 min and never invalidated when tickets/claims change; CLAUDE.md guidance (`Cache::get` + conditional `put`) is followed — keep it. But the database cache driver plus 15-min TTL means two users of one broker get consistent but stale data; document or add a manual refresh. | Broker controllers | Add a "refresh" action calling `Cache::forget`. |
| M-10 | **Queue configuration.** Jobs use `--tries=1` (per CLAUDE.md) and have no `$tries`, `$backoff`, `failed()` handler, or `ShouldBeUnique`. A transient NIIP failure is caught and written to `niip_status` rather than failing the job, so the framework never retries, and manual retry relies on string patterns (`'Array'`, `'retry_queued'`). Dispatching inside request handlers should use `afterCommit()`. | `PostNIIPDataSlow`, `CheckEcmrJob`, `PolicyController` | Make the job throw on retryable errors with `$tries = 3`, `backoff()`, `ShouldBeUnique` keyed on policy number, and store structured status (success/failed + message) instead of raw response bodies. |
| M-11 | **Cancellation does not notify Elite/NIIP.** `cancelpolicy` only updates the local row and refunds credit. The policy remains valid at the insurer and registry. Also the refund logic is duplicated 4× and uses non-atomic counters (C-6); the flash message claims "credit refunded" even when none was. Superadmin-only is correct; add a confirmation + audit log. | `PolicyController::cancelpolicy` | Call Elite cancel endpoint (if available) and extract a `CreditService`. |
| M-12 | **Date / timezone handling.** Dates stored as `Y/m/d` strings in policies (`start_date`, `end_date`); policy dates use `date_create()` in the API controller (server time) vs `Carbon` elsewhere. `end_date = start + 1y - 1 day` in one place and `start + 1y` in the API. Inconsistent coverage periods. | `PolicyController`, `ThirdPartyController` | Use `date` columns, `config('app.timezone')=Africa/Lagos`, one period rule. |

---

## Low / Code quality

| ID | Finding | Where |
|----|---------|-------|
| L-1 | **Leftover debug code:** `dd($renewals)` (`DashboardController:264`), `dd([...])` and `dd($data)` in `VehiclecolorController:55,74` (a route reachable by authenticated users that kills the request), commented `dd` calls. | controllers |
| L-2 | **`PolicyController` is 1,311 lines** mixing validation, user creation, payment, Elite calls, credit accounting, reporting and CSV. Extract `PolicyIssuanceService`, `CreditService`, `ElitePolicyClient`, `NiipPayloadBuilder`, Form Requests. The Elite payload is built identically in `paypolicy` and `ThirdPartyController`. |
| L-3 | **Empty resource stubs** (`create/store/show/edit/update/destroy`) across `PolicyController`, `AgentsdetailsModelController`, `PaystacktransactionController`, `NinController`, … Remove dead methods. |
| L-4 | **Naming consistency:** models `policy`, `policyrisk`, `agentsdetailsModel`, `states`, `lga` (lower-case, singular/plural mixed); file `niipmanualController.php` vs class `NiipManualController` (autoload breaks on Linux; the route imports `NiipManualController`), `ECMRController` vs `EcmrController` in routes (`ecmr_index`) — **case-sensitive filesystems on Linux will 500** on `ECMRController::class`. Verify and fix; PSR-4 + StudlyCase for models. |
| L-5 | **Hard-coded business values:** fallback GSM `+234 806 565 7291` and `08065657291`, `info@salamtakafulinsurance.com`, contribution amounts, Elite `engineCapacity: "1.6L"`, `EngineCap: 3`, `PolicyHolderNIN: '  '`. Move to config/DB (`products` table exists). |
| L-6 | **Typos / loose comparisons:** `Sucessful`, `Pament`, `emcr_check`, `adress`, `==` vs `===` on money and ids, `Auth::check();` as a no-op statement (`paypolicy`, `listpolicySubagents`, `cancelpolicy`). |
| L-7 | **Inline `env('PAYSTACK_ELITE_TOKEN')`** fallback leaves `$token` undefined when neither branch matches (see C-5). |
| L-8 | **Hand-rolled role checks** `in_array($user->role, ['admin','superadmin'])` appear 30+ times. Add `User::isAdmin()`, `hasRole()`, and an `EnsureRole` middleware (also fixes C-3). Consider a `UserRole` backed enum cast. |
| L-9 | **Database files in the repo:** `database/database.sqllite` (empty, typo'd extension) and the `*.session.sql` editor scratch files (`salam.session.sql` contains `update users set password=(select password …) where id=47`) are tracked in git. `.gitignore` ignores `.sql` but they were committed earlier. `git rm --cached` them and add to ignore. Local `database/database.sqlite` is ignored (good). Also an untracked `storage/framework/lsp-*.php` appears in `git status`. |
| L-10 | **Passwords:** minimum lengths of 6 on sub-agent registration and reset; align with `Password::defaults()` and enforce uniformly. |
| L-11 | **Documentation drift:** `CLAUDE.md` says broker accounts are created `pending` then approved; `EliteBrokerController::createBrokerAccount` creates them `active` (see C-3). Update one or the other. `README.md` is still the Sneat starter. |
| L-12 | **Frontend:** Chart.js loaded from a CDN inline with a pinned version but no SRI hash — add `integrity` + `crossorigin`. Verify Blade output uses `{{ }}` escaping for user data (a grep for `{!!` in `resources/views` returned no matches). |

---

## Things done well (keep these)

- Broker portfolio cache pattern (`Cache::get` + conditional `Cache::put`) avoids caching empty proxy failures.
- Public claim flow uses throttling, session PRG, and a signed, short-lived brown-card download URL.
- Queued NIIP/ECMR work, with a failed-jobs monitor (`AdminJobController`) that does check for admin role.
- Broker ticket/claim *record* access is checked per owner (`user_id`) on show/reply.
- Login enforces `account_status` (pending/rejected cannot sign in).
- Chassis validation, `escapeshellarg` use in `ProxyClient`, CSV export is chunked and role-scoped.
- `User` model hashes passwords via cast and hides `password`/`remember_token`.

---

## Suggested remediation plan

1. **Week 1 — stop the bleeding (C-1…C-4, C-7):** `EnsureRole` middleware on every admin route group; `PolicyPolicy` for policy access; disable or lock down registration; rotate any Elite tokens/API tokens that may have been exposed (`list_agents` rendered them to all users — treat as compromised if prod has been reachable by self-registered accounts). Review the `users` table for unexpected `admin`/`superadmin` roles and the `agentsdetails_models` table for unexpected credit/`auth_token` changes.
2. **Week 2 — money (C-5, C-6, H-6, H-7):** payment state machine, single-use references, atomic credit reservation + ledger, finish or remove `PolicyPaymentService`.
3. **Week 3 — API & secrets (H-1, H-2, H-4, H-5):** remove default passwords, move env usage into config, encrypt tokens, replace `shell_exec` curl.
4. **Week 4 — resilience & quality (M-*, L-*, H-8, H-9):** queue retry strategy, imports as jobs, tests for every role × route, break up `PolicyController`.

---

## Coverage / not yet reviewed

Reviewed in depth: `routes/*`, `UserController`, `AgentsdetailsModelController`, `EliteBrokerController`, `AdminJobController`, `ThirdPartyController`, `PolicyController` (issuance, payment, cancel, retry, parts of reporting), `PaystacktransactionController`, `PolicyPayment*`, `ProxyClient`, `PostNIIPDataSlow`, `BrokerTicketController`, `BrowncardController`, `NiipManualController`, `User` model, `register`/login Volt components (partially).

**Still to review (next pass):**
- `DashboardController` (429 lines) and the remaining ~900 lines of `PolicyController` (report filters, renewals, `buypolicy`)
- `CheckEcmrJob`, `EcmrService`, `ClaimController`, `ClaimNotificationController::lookupPolicy`/`fetchFromElite`, `BrokerClaimNotificationController::store`, `AdminBrokerTicketController`
- Remaining models (relationships, `$fillable`/`$guarded`, casts), Mailables and Notifications
- Blade views (XSS, hidden credential output such as `auth_token`, CSRF tokens on forms), menu components (role-based visibility is not security)
- Migrations (indexes on `policyno`, `regno`, `telno`, `email`, `agent_id`; unique constraints on `reference_code`; FK constraints)
- `config/*` (CORS, Sanctum expiry, session, logging), `composer.json` dependency audit (`composer audit`, `npm audit`)
- Verify production `.env` values (`APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL`, HTTPS-only cookies)
- Web server config (block `/storage` private paths, `.env`, HTTPS/HSTS)

---

## Change Log

| Date | Author | Change |
|------|--------|--------|
| 2026-11-02 | Claude (Review #1) | Initial review created: 8 Critical, 9 High, 12 Medium, 12 Low findings. No code changed. |
| 2026-11-02 | Claude (Fix #2) | High items: `ThirdPartyController` hardened (agent gate, random password, no body logging, null checks); `submitmpolicy` random password + role-restricted phone match; new `users:reset-default-passwords` command; `ProxyClient` rewritten (proc_open, secret via stdin, status logging); `agentsdetailsModel.auth_token` encrypted (+ migration) and masked in the admin UI; `PaystacktransactionController` rewritten on `Http`; `PolicyPaymentService` guarded; NIIP manual import moved to queued `ProcessNiipManualImport` and `NiipManualController` renamed; brown-card name masking; per-policy claim-lookup limiter; `.env.example` and `config/variables.php` cleaned. New tests `HighItemsTest.php`. |
| 2026-11-02 | Claude (Fix #1) | Critical items C-1…C-8 addressed (C-4 partially). New: `EnsureRole` middleware (`role:` alias), `PolicyPolicy`, route regrouping in `routes/web.php`, hardened `UserController`, `AgentsdetailsModelController`, `PolicyController::paypolicy` (+ `reserveCredit`/`releaseCredit`), `PolicyPaymentController`, `PaystacktransactionController` (config instead of `env()`), registration token removed, `test_async` removed. Added `tests/Feature/AuthorizationTest.php`. |

<!-- How to update this file:
  1. When fixing an item, set its Status to FIXED and add "(commit abc1234, YYYY-MM-DD)".
  2. Add a row to the Change Log.
  3. New findings: append with the next ID in the right severity section.
  4. After each review pass, update the Summary counts and the Coverage section.
-->
