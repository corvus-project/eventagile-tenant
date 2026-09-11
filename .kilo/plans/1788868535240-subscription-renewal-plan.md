# Plan: Subscription Renewal Service

## Overview
Implement automatic renewal of expired active subscriptions. When an active subscription reaches `ends_at`, clone it into a new subscription with extended dates, mark the previous subscription as `expired`, and keep the tenant's access continuous.

## Current State Analysis

| Finding | Detail |
|---|---|
| `renew()` doesn't exist | `app/Console/Commands/RenewSubscription.php` calls `$subscription->renew()`, but `Subscription` has no such method → guaranteed `BadMethodCallException` |
| No scheduler | `routes/console.php` only defines `inspire`; the command never runs automatically |
| Tenant site access check | Queries `status = 'active'` + `latest('starts_at')`; no `starts_at <= now()` filter. A future-dated new subscription would break access |
| Grace period exists | Tenant site's `isInGracePeriod()` covers the gap between `ends_at` and renewal |
| Date math inputs exist | `interval` (day/week/month/year) + `interval_count` on each subscription |
| Renewal audit columns exist | `renewal_status`, `renewal_date` in migration but unused |
| No Stripe integration | `stripe_*` fields exist but are unused; renewal is purely internal |

## Confirmed Design Decisions

1. **Renewal timing — Option A**: Renew when `ends_at <= now()`. New subscription `starts_at = old.ends_at` (back-dated for continuity, no future-dated issues with tenant site).
2. **New `ends_at`**: `old.ends_at + interval × interval_count` (using `PlanInterval` values: day/week/month/year).
3. **Logic location**: Add `renew(): bool` to the `Subscription` model (matches the existing command's `$subscription->renew()` call; keeps the command thin).
4. **Concurrency safety**: `renew()` re-fetches the row with `lockForUpdate()` inside a DB transaction, then re-validates status/dates before cloning. Prevents duplicate renewals under concurrent command runs.
5. **Old subscription**: `status = 'expired'`, `renewal_status = 'completed'`, `renewal_date = now()`.
6. **New subscription**: Clone all attributes via `replicate()`, override `starts_at`/`ends_at`/`status = 'active'`, leave `renewal_status`/`renewal_date` null. Stripe IDs are copied (no Stripe integration exists; preserves billing relationship).
7. **Scheduling**: Add `Schedule::command('renew-subscription')->daily()` in `routes/console.php` (Laravel 12 pattern).
8. **Testing**: Pest tests using time-freezing (`Carbon::setTestNow()`) for deterministic date math.

## Implementation Steps

### 1. Add `renew()` to `app/Models/Subscription.php`
- Guard: `status === 'active'`, `ends_at` exists, `ends_at <= now()` → else return `false`
- Wrap in `DB::transaction()`; re-fetch with `lockForUpdate()` and re-validate
- Compute `newEndsAt` via `interval` + `interval_count` (default `interval_count` to 1, default unknown interval to month)
- `replicate()` → override `starts_at`, `ends_at`, `status` → `save()`
- Mark old: `status = 'expired'`, `renewal_status = 'completed'`, `renewal_date = now()` → `save()`
- Return `true` on success

### 2. Update `app/Console/Commands/RenewSubscription.php`
- Query: `where('status', 'active')->where('ends_at', '<=', now())->get()`
- Iterate, call `renew()`, count successes
- Log result with `info()`; return `self::SUCCESS`
- Update the command description

### 3. Add scheduling in `routes/console.php`
- `Schedule::command('renew-subscription')->daily();`

### 4. Add Pest tests
- `renew()` clones with correct `starts_at`/`ends_at` and marks old `expired`
- Non-`active` subscription is not renewed
- Not-yet-due subscription (`ends_at > now()`) is not renewed
- Idempotency: running `renew()` twice doesn't create a second clone
- Command processes all due subscriptions and reports count

## Validation Steps
- Run the Pest test suite
- Run `php artisan renew-subscription` manually against seeded/test data
- Verify a renewed subscription keeps `status = 'active'` with `starts_at = old.ends_at`
- Verify the old subscription is `expired` with `renewal_status = 'completed'`
- Confirm the tenant site still grants access (new subscription is the latest active one, `starts_at <= now()`)

## Out of Scope
- Stripe webhook/payment integration
- Changes to the tenant site codebase
- Adding `active()`/`isActive()` scopes to the central `Subscription` model
- Plan/price changes during renewal