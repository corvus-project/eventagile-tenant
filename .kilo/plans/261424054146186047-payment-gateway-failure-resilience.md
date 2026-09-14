# Payment Gateway Failure Resilience Plan

## Context

When the payment gateway is down or in maintenance mode, subscription renewals and account setup fail silently in console commands. Users have no visibility into these failures because everything runs in the background. This plan ensures graceful degradation, clear notifications, and user-visible payment status.

## Important Details

- Payment failures are currently silent (logged to Laravel log only).
- The `RenewSubscription` and `AccountSetup` commands run via cron/console with no user-facing output.
- The `subscription-expired.blade.php` page is the only failure state users see — but it's too late (subscription already expired).
- The `Subscription` model already has `last_payment_status`, `last_payment_date`, `renewal_status` fields — perfect for tracking failures.
- `User` model uses `Notifiable` trait — Laravel notifications work.
- Existing mail mailable pattern: `app/Mail/*.php` (e.g., `NewEventRegistration`, `EventRegistrationUpdated`).

---

## Phase 1 — Payment Service Failure Awareness

### Modify: `app/Services/FakePaymentGateway.php` (created in payment plan)

On payment failure, return structured response including:

```php
[
    'success' => false,
    'transaction_id' => null,
    'message' => 'Payment gateway unavailable. Please try again later.',
    'code' => 'gateway_timeout', // or 'card_declined', 'insufficient_funds', 'maintenance_mode'
    'retryable' => true, // can be retried automatically
    'retry_after' => 300, // seconds to wait before retry (5 min)
]
```

### Modify: `app/Models/PaymentTransaction.php` (created in payment plan)

Add fields to track failure context:
- `failure_code` — machine-readable code (gateway_timeout, card_declined, etc.)
- `failure_message` — human-readable message
- `retry_count` — how many times this transaction has been retried
- `next_retry_at` — when to retry (nullable)
- `status` — expanded: `pending`, `succeeded`, `failed`, `retrying`, `permanently_failed`

### Modify: `app/Services/SubscriptionService.php`

Add a method to check if a failure is retryable:

```php
public function isPaymentFailureRetryable(PaymentTransaction $transaction): bool
{
    if ($transaction->status === 'permanently_failed') {
        return false;
    }
    if ($transaction->retry_count >= 3) {
        return false;
    }
    if ($transaction->next_retry_at && now()->lt($transaction->next_retry_at)) {
        return false;
    }
    return in_array($transaction->failure_code, [
        'gateway_timeout',
        'gateway_unavailable',
        'maintenance_mode',
        'temporary_network_error',
    ], true);
}
```

---

## Phase 2 — Graceful Degradation in Renewal

### Modify: `RenewSubscription` command

**Current flow:** Find expired subs → charge → renew or skip on failure.

**New flow:** Find expired subs → charge:
- **Success**: Proceed with renewal (unchanged)
- **Retryable failure** (gateway timeout, maintenance):
  1. Log the failure with details
  2. Mark `PaymentTransaction` status as `retrying`, increment `retry_count`, set `next_retry_at` to 5 min later
  3. Update subscription `renewal_status = 'pending'` (NOT `payment_failed`)
  4. **Do NOT expire the subscription** — tenant keeps access
  5. Send admin notification (email) about gateway issue
  6. Next cron run will retry (since `renew()` checks `ends_at <= now()`, and if subscription is still valid due to grace period, it will retry)
- **Non-retryable failure** (card_declined, insufficient_funds):
  1. Log failure
  2. Mark `PaymentTransaction` status as `permanently_failed`
  3. Update subscription: `renewal_status = 'payment_failed'`, `last_payment_status = 'failed'`
  4. Send user email: "Your subscription renewal failed — please update payment method"
  5. Tenant enters grace period (if applicable) or gets locked out after grace expires

### Key logic: Don't immediately expire on payment gateway failure

The subscription's `ends_at` should NOT be changed when payment fails due to a retryable error. Only change subscription state when:
- Payment is permanently declined (card issue)
- Max retry attempts exhausted (3 failures over 24 hours)
- Subscription is past grace period

This means: **Gateway downtime = no user impact. Tenant keeps access until we determine the failure is permanent.**

### Modify: `app/Models/Subscription.php` — `renew()` method

Before creating a new subscription record, check if we're in a retryable failure state:

```php
public function renew(): bool
{
    // Check if we're in a retryable failure state — skip renewal until resolved
    $latestPayment = $this->payments()->latest()->first();
    if ($latestPayment && $latestPayment->status === 'retrying') {
        return false; // Will be retried next cron run
    }

    // ... existing renew logic
}
```

---

## Phase 3 — AccountSetup Payment Failure

### Modify: `AccountSetup` command

**SETUP action with retryable payment failure:**
1. Log failure
2. Keep `AccountSetup` record action as `SETUP` (not FINISHED)
3. Set `error_message` on AccountSetup with failure details
4. Send admin notification about gateway issue
5. Tenant is still created (don't block tenant creation due to payment)
6. Subscription is NOT created yet
7. Next cron run retries: on next run, if payment succeeds → create subscription → mark AccountSetup as FINISHED

**SETUP action with non-retryable payment failure:**
1. Tenant is still created
2. Subscription is NOT created
3. AccountSetup marked as FINISHED but with `payment_failed` flag
4. User receives email: "Your account is active (trial) but payment for the plan failed. Please update your payment method."
5. User operates under trial period until payment is resolved

---

## Phase 4 — User-Facing Notifications

### New Mailable: `app/Mail/SubscriptionPaymentFailed.php`

**Triggered when:** Non-retryable payment failure during renewal or setup.

**Content:**
- Subject: "Action Required: Your Subscription Payment Failed"
- Body: Plain explanation of failure, what to do next, link to contact support
- Footer: "This is an automated message. Please do not reply."

### New Mailable: `app/Mail/SubscriptionRenewalGatewayDown.php`

**Triggered when:** Retryable payment failure (gateway down/maintenance).

**Content:**
- Subject: "Subscription Renewal In Progress — We'll Notify You When Complete"
- Body: "Our payment system is experiencing a temporary issue. Your subscription will be renewed automatically once the issue is resolved. Your account access is not affected."
- No action needed from user

### New Mailable: `app/Mail/SubscriptionRenewed.php`

**Triggered when:** Payment succeeds after previous failure (recovery).

**Content:**
- Subject: "Your Subscription Has Been Renewed"
- Body: Confirms renewal, new period dates, amount charged
- Reassurance that no action needed

### Notification Classes

For admin/owner notification (in-app or email):
- `App\Notifications\PaymentGatewayDown` — sent to tenant owner when retryable failure occurs
- `App\Notifications\PaymentPermanentlyFailed` — sent to tenant owner + admin when non-retryable failure occurs

These use Laravel's `Notification` class (User has `Notifiable` trait, so `$user->notify()` works).

---

## Phase 5 — Subscription Status Visibility

### Create a Subscription Status Page

**New view:** `resources/views/pages/tenants/subscription-status.blade.php`

**Shows for the authenticated tenant user:**
1. Current subscription status (active, trial, expired, payment_failed, cancelled)
2. Plan name and price
3. Trial period (if applicable): "Your trial ends on Jan 15, 2026"
4. Payment history (last 5 transactions from `payment_transactions` table):
   - Date | Amount | Status | Failure reason (if applicable)
5. Next billing date
6. If payment failed: "Action needed" banner with explanation
7. If gateway issue: "Payment system temporarily unavailable — your access is not affected"
8. Contact support link/button

### Add Route

`GET /subscription/status` → `SubscriptionStatusController@show`

### Modify: `SubscriptionService::canWithReason()`

Return additional context for all actions:

```php
return [
    'allowed' => false,
    'reason' => 'Your subscription payment failed. Please contact support.',
    'subscription_status' => 'payment_failed',
    'payment_failed_at' => $subscription->last_payment_date,
    'payment_failure_reason' => $latestTransaction->failure_message,
    'retryable' => false,
];
```

This allows the frontend to show specific messages based on the failure type:
- `retryable: true` → "We're experiencing a temporary issue..."
- `retryable: false` → "Your payment failed. Please update your payment method..."

---

## Phase 6 — Auto-Retry Mechanism

### Modify: `RenewSubscription` command

Add a `--retry` flag and automatic retry logic:

```php
public function handle(): int
{
    // Normal run: process subscriptions that need renewal
    $subscriptions = Subscription::query()
        ->where('ends_at', '<=', now())
        ->whereNotIn('renewal_status', ['completed', 'payment_failed'])
        ->get();

    // Also check for retrying payments that are ready
    $retrying = PaymentTransaction::query()
        ->where('status', 'retrying')
        ->where('next_retry_at', '<=', now())
        ->where('retry_count', '<', 3)
        ->get();

    foreach ($retrying as $transaction) {
        $this->retryPayment($transaction);
    }

    // ... normal renewal logic
}
```

### Retry Logic (`retryPayment`)

```php
private function retryPayment(PaymentTransaction $transaction): void
{
    $subscription = $transaction->subscription;
    if (! $subscription) return;

    $result = app(PaymentService::class)->charge(
        $subscription->stripe_customer_id,
        $subscription->amount,
        $subscription->currency,
        ['subscription_id' => $subscription->id, 'retry' => true]
    );

    if ($result['success']) {
        // Update transaction status to succeeded
        // Create new PaymentTransaction for successful retry
        // Call $subscription->renew()
        // Send "Subscription Renewed" email to user
        // Log success
    } else {
        // Increment retry_count
        // Update next_retry_at (exponential backoff: 5min, 15min, 45min)
        // If retry_count >= 3, mark permanently_failed, send failure email
    }
}
```

### Exponential Backoff Schedule

| Retry # | Wait After | Total Time |
|----------|-------------|------------|
| 1 | 5 minutes | 5 min |
| 2 | 15 minutes | 20 min |
| 3 | 45 minutes | ~1 hr 5 min |
| 4+ | Permanently failed | — |

---

## Phase 7 — Dashboard Admin Visibility

### Modify: Admin Dashboard (existing or new)

Add a "Payment Health" section to admin dashboard showing:
1. Number of subscriptions with `last_payment_status = 'failed'`
2. Number of subscriptions with `renewal_status = 'pending'` (waiting for retry)
3. Number of `PaymentTransaction` with status `retrying`
4. Recent payment failures (last 10) with tenant name, failure reason, date
5. Gateway uptime percentage (based on fake gateway logs)

### New Admin Route

`GET /admin/payment-health` → `AdminPaymentController@index`

---

## Summary of Files to Create/Modify

### New Files
- `app/Services/PaymentService.php` — interface (from payment plan)
- `app/Services/FakePaymentGateway.php` — fake implementation (from payment plan)
- `app/Mail/SubscriptionPaymentFailed.php` — user email for non-retryable failure
- `app/Mail/SubscriptionRenewalGatewayDown.php` — user email for retryable failure
- `app/Mail/SubscriptionRenewed.php` — user email for successful renewal
- `app/Notifications/PaymentGatewayDown.php` — admin in-app notification
- `app/Notifications/PaymentPermanentlyFailed.php` — admin notification
- `app/Http/Controllers/SubscriptionStatusController.php` — status page
- `app/Http/Controllers/AdminPaymentController.php` — admin payment health
- `resources/views/pages/tenants/subscription-status.blade.php` — user status page
- `resources/views/pages/admin/payment-health.blade.php` — admin dashboard
- `tests/Feature/PaymentGatewayFailureTest.php` — failure handling tests

### Modified Files
- `app/Services/SubscriptionService.php` — retryable failure detection, enriched response
- `app/Models/Subscription.php` — check retry state before renew, `renewWithPayment()` enhancement
- `app/Console/Commands/RenewSubscription.php` — retry logic, graceful failure handling
- `app/Console/Commands/AccountSetup.php` — SETUP failure handling
- `app/Models/PaymentTransaction.php` — retry fields, status enum expansion
- `routes/tenant.php` — subscription status route
- `routes/admin.php` — payment health route
- `resources/views/pages/tenants/subscription-expired.blade.php` — enhanced with specific failure messages

---

## Key Design Decisions

### 1. Gateway Down = No User Impact
When the payment gateway is unavailable, tenants keep access. The subscription doesn't expire. The next cron run automatically retries.

### 2. Only Permanent Failures Block Access
Card declines, insufficient funds, etc. are real failures. After 3 retries over ~1 hour, if all fail, the subscription is marked `payment_failed` and the user enters the normal grace period → expiry flow.

### 3. Users Know What's Happening
Three types of messages:
- **Gateway down**: "Your subscription is being renewed automatically. No action needed."
- **Permanent failure**: "Your payment failed. Please update your payment method or contact support."
- **Success after failure**: "Your subscription has been renewed successfully."

### 4. Admins Have Visibility
Admin dashboard shows payment health metrics so ops teams can intervene if the gateway is down for extended periods.

### 5. Stripe-Ready
All gateway interaction goes through `PaymentService` interface. When ready to use real Stripe:
1. Create `app/Services/StripePaymentGateway.php` implementing `PaymentService`
2. Update `config/payment.php` to use `stripe` gateway
3. All existing code works unchanged — failure handling, retries, notifications all work the same way (Stripe returns different error codes, but our retry logic handles all of them)
