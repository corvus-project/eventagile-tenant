# Subscription & Plan Enforcement Plan

## Goal & Scope

Make the tenant subscription system safe to go live: centralize "is this subscription valid", gate site access, enforce `max_events`, `max_registrations`, and `sending_emails` at the point of persistence (not just UI), and fix the existing bugs.

**Architecture is sound** — central SQLite/MySQL holds `plans` + `subscriptions`, each tenant has its own DB holding `events`/`event_registrations`/`users`. This is worth keeping; **no full rewrite required**. The work is targeted refactoring of `SubscriptionService`, the `Subscription`/`Plan` models, middleware, and the Livewire form/store paths. Stripe/checkout/webhooks are explicitly **out of scope** here (subscriptions are created manually on onboarding with the free plan; Stripe fields exist on the table but no gateway integration is wired).

## Current Architecture (key facts)
- `stancl/tenancy` v3. Central DB = `database.sqlite` (MySQL in prod). Tenant DBs = `database/tenants/<tenant-id>.sqlite`.
- `Subscription` model uses `CentralConnection` → always queries central DB. `Event`/`EventRegistration` do not (tenant DB). 
- Onboarding (`OnboardingUser` listener → `OnBoardingService::createSubscriptions`) creates one `active` free subscription with `ends_at = +1 year`.
- Enforcement today lives in `SubscriptionService::can(Tenant, action)` dispatched via `Tenant::able()`.

## Findings — Go-Live Blockers / Bugs

1. **Expired subscriptions are treated as active.** `getActiveSubscription()` only filters `status='active'`; it never checks `starts_at`/`ends_at`. A subscription whose `ends_at` is in the past still passes → site stays open. **Critical.**
2. **Event limit never actually blocks.** `createEvent()` counts `Event::where('status','active')` — but `EventStatus` enum values are `'Scheduled','Completed'` etc.; `'active'` matches nothing → count is always 0 → always allowed. **Critical.**
3. **Checks only at page mount, not at save.** `create.blade.php` sets `$eventLimit` in `mount()`, but `EventForm::store()` and `cloneEvent()` never re-check → bypassable for anyone who can call the Livewire action. **Critical.**
4. **`max_registrations` is semantically wrong.** `CapacityLimit` validation rule is applied to the event's `capacity` field and compares `capacity > max_registrations`. That conflates per-event capacity with a tenant-wide registration quota and blocks valid capacities. No actual total-registration limit is enforced. **Bug.**
5. **`sending_emails` is never enforced.** `EventRegistrationForm::store()` always queues `NewEventRegistration`; `registration.blade.php` always sends `EventRegistrationUpdated` (the "approval/update" email the free plan should be denied). **Gap.**
6. **No site-wide access gate.** Nothing stops a visitor/admin from using a tenant site whose subscription expired/cancelled. **Gap (explicitly requested).**
7. **Dead attribute + inverted logic.** `registration_ends_at` is referenced in `view.blade.php` and `[Event].blade.php` but the column is `registration_deadline`; `getShowForm()` condition `> now()` would (if the attr existed) block live events. **Bugs.**
8. **Model mass-assignment gaps.** `Subscription::$fillable` omits most migration columns; `Plan` has no `$fillable` and no casts; `SubscriptionFactory` references a non-existent `user_id` column. **Risk.**
9. **`Plan` not central-scoped.** `Plan` lacks `CentralConnection`; works only because it's never queried during a tenant request. Latent bug if that changes. **Risk.**
10. **`getActiveSubscription` ordering.** Uses `->first()` with no ordering; multiple subscriptions (free + paid) resolve to an arbitrary row. **Risk.**
11. **Broken test.** `CreateEventTest` references `App\Livewire\Events\CreateEvent`, which does not exist (create page is an inline Livewire component). **Risk.**

## Decisions (resolved, recommended)

- **D1 — Subscription validity** = `status === 'active'` AND `starts_at <= now()` AND (`ends_at` IS NULL OR `ends_at >= now()`). Put this in a single `Subscription::isActive()` + `scopeActive` so every caller is consistent.
- **D2 — Site-wide gate (middleware)** `EnsureTenantHasActiveSubscription` applied to the tenant route group (after tenancy init). Allowlist routes that must work without a valid subscription: login, register, password reset, email verify, impersonate callback. Everything else redirects to a `subscription-expired` landing page. **Recommended** because the user said expired subs must not use the site at all.
- **D3 — `max_registrations` = tenant-wide TOTAL across all events** (recommended), not per-event. Rationale: a per-event attendee ceiling is already the `capacity` field; a plan-level `max_registrations` is the natural "seats/registrant quota". Count `EventRegistration` rows where `is_attending = true` (actual sign-ups) across all the tenant's events; block new public bookings in `EventRegistrationForm::store()` when the total would exceed the limit. *Alternative (per-event cap via a separate `max_capacity_per_event` plan feature) left as a future toggle.*
- **D4 — `sending_emails` enforcement** = boolean plan limitation. Gate both the registration-confirmation email (`EventRegistration::store` path) and the registration-status-update email (`registration.blade.php::save`). When false, skip `Mail::` calls.
- **D5 — `max_events` enforcement** at persistence: re-check in `EventForm::store()` and `cloneEvent()` (server-side), and keep the mount-time UI flag for UX. Count events regardless of status (drafts count toward the quota) — or only non-deleted; recommend counting all non-trashed events so organizers can't evade by drafting.
- **D6 — `CapacityLimit` rule** removed from event `capacity`. Capacity validated as `required|integer|min:1`. Decoupling is correct per D3/D5.
- **D7 — Replace fragile dynamic dispatch** with an explicit `can(Tenant, string)` using a `match` (or method map), returning `(bool, reason)`. Keep `Tenant::able()` as the caller facade.
- **D8 — `Plan` model** add `CentralConnection` + `$fillable` + JSON casts for `features`/`limitations` + accessors (`limitations()`, `features()`).
- **D9 — Tests** fix `CreateEventTest` target; add unit tests for `SubscriptionService::can` (event quota) and `Subscription::isActive` (expiry); add a Livewire test that creating an event beyond the limit is rejected server-side.

## Ordered Implementation Tasks

### Phase 1 — Core model & service correctness
1. `app/Models/Subscription.php`
   - Expand `$fillable` (or `$guarded=[]`); add `isActive()`/`isExpired()` methods + `scopeActive`.
   - Cast `starts_at`/`ends_at` datetime (already done for some). Add `status` handling.
2. `app/Models/Plan.php`
   - Add `CentralConnection`, `$fillable`, JSON casts for `features`/`limitations`, accessor methods.
3. `app/Services/SubscriptionService.php`
   - Rewrite `getActiveSubscription`: order by `starts_at desc`, use `scopeActive` (status + starts_at/ends_at).
   - Replace dynamic `can()` dispatch with explicit `match` returning `(bool, reason)`.
   - Fix `createEvent` count (drop the bogus `status='active'` filter; count non-trashed events).
   - Add `sendEmails(Tenant): bool` and `registrationsTotal(Tenant): int` helpers.
   - Change fallbacks: when no active sub, `max_events`/`max_registrations` default to `0` (deny), not `10`/`0` inconsistently.

### Phase 2 — Site-wide access gate
4. Create middleware `app/Http/Middleware/EnsureTenantHasActiveSubscription.php` (central-aware: queries `Subscription` on central). Allowlist auth/verify/impersonate routes; otherwise redirect to a new `subscription-expired` Livewire page (or 403 view) for tenants with invalid subs.
5. Register + apply the middleware to the tenant route group in `routes/tenant.php` (high priority, after tenancy init). Add the allowlisted public/visitor route exceptions.

### Phase 3 — Enforcement at persistence points
6. `app/Livewire/Forms/EventForm.php`
   - In `store()`: call `SubscriptionService::can(tenant, 'create-event')`; if denied, abort with the reason (not just UI flag).
   - Remove `new CapacityLimit()` from the `capacity` rule.
7. `resources/.../events.blade.php` `cloneEvent()`
   - Enforce `create-event` before cloning; if denied, abort.
8. `app/Livewire/Forms/EventRegistrationForm.php`
   - In `store()`: if no active subscription → block; else if `registrationsTotal >= limit` → block (return validation error). Keep event `capacity` check.
   - Gate the `Mail::queue(new NewEventRegistration(...))` by `plan_limitations['sending_emails']`.
9. `resources/.../registration.blade.php` `save()`
   - Gate `Mail::queue(new EventRegistrationUpdated(...))` by `sending_emails`.
10. Fix `registration_deadline`/`getShowForm` logic in `tenants/view.blade.php` and `events/[Event].blade.php` (use `registration_deadline`, correct the comparison; it should block when `registration_deadline < now()`).

### Phase 4 — Cleanup & supporting
11. Update `PlanSeeder` so `Enterprise Plan` limitations are consistent JSON (it currently has `['Unlimited events']` string, not a `max_*` shape). Decide "unlimited" representation (e.g. `null`/`-1` = unlimited) and make `SubscriptionService` honor it.
12. Fix `SubscriptionFactory` (drop `user_id`).
13. Remove `app/Rules/CapacityLimit.php` (task 6) or repurpose it to the total-registration check at booking time.
14. Add a `Subscription` ↔ `Tenant` accessor/`activeSubscription()` relation on `Tenant` for ergonomic reads.

### Phase 5 — Tests & validation
15. Fix `CreateEventTest` (target the inline component class path or convert to a class-based component).
16. Add unit tests: `SubscriptionService::can` event-quota; `Subscription::isActive` expiry; `max_registrations` total counting.
17. Add Livewire test: event creation denied when over quota; registration denied when over total limit; emails skipped when `sending_emails=false`.

## Risks & Mitigations
- **Tenancy in tests:** `stancl/tenancy` + `RefreshDatabase` doesn't auto-seed tenant DBs. Mitigate: use `RefreshDatabase` on central for model/service unit tests; for Livewire enforcement tests, initialize a tenant via `Tenant::create` + `database()->create()` + migrate, or use the `stancl/tenancy` testing trait. Flag `phpunit.xml` DB_DATABASE=testing — ensure tenant SQLite files are created under `database/tenants/`.
- **Public landing access during trial/first run:** gating the whole site means even the free plan blocks after `ends_at`. Make sure onboarding `ends_at` is far enough / add a grace period field if needed. (Out of scope now; document.)
- **`Plan` queried during tenant context:** fixed by adding `CentralConnection`.
- **Concurrency (race):** two simultaneous registrations could both pass the total check. Accept for now; recommend a `unique` index / row lock later if scale demands.

## Validation Steps
- `php artisan test` (after adding `TELESCOPE_ENABLED`-friendly central+tenant DB in `phpunit.xml`).
- Manual: create a tenant (free, max_events=3), create 3 events → 4th creation is rejected; expired `ends_at` → site shows the expired page for all non-auth routes; free plan booking still emails are suppressed.

Saved plan: `.kilo/plans/1788361363223-subscription-enforcement-plan.md`
