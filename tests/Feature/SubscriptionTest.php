<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reports an active subscription as active', function () {
    $sub = new Subscription;
    $sub->status = 'active';
    $sub->starts_at = now()->subDay();
    $sub->ends_at = now()->addDays(5);

    expect($sub->isActive())->toBeTrue()
        ->and($sub->isExpired())->toBeFalse();
});

it('reports an active subscription with a future start as inactive', function () {
    $sub = new Subscription;
    $sub->status = 'active';
    $sub->starts_at = now()->addDay();
    $sub->ends_at = now()->addDays(5);

    expect($sub->isActive())->toBeFalse();
});

it('reports an active subscription with a past end as inactive', function () {
    $sub = new Subscription;
    $sub->status = 'active';
    $sub->starts_at = now()->subDays(5);
    $sub->ends_at = now()->subDay();

    expect($sub->isActive())->toBeFalse()
        ->and($sub->isExpired())->toBeTrue();
});

it('allows access to a cancelled subscription that is still within the grace window', function () {
    $sub = new Subscription;
    $sub->status = 'cancelled';
    $sub->starts_at = now()->subDay();
    $sub->ends_at = now()->addDays(5);

    expect($sub->isActive())->toBeTrue()
        ->and($sub->isCancelled())->toBeTrue()
        ->and($sub->isInGracePeriod())->toBeTrue();
});

it('blocks access to a cancelled subscription that has passed the grace window', function () {
    $sub = new Subscription;
    $sub->status = 'cancelled';
    $sub->starts_at = now()->subDays(10);
    $sub->ends_at = now()->subDay();

    expect($sub->isActive())->toBeFalse()
        ->and($sub->isCancelled())->toBeTrue()
        ->and($sub->isInGracePeriod())->toBeFalse();
});

it('treats a null end date as active for active status', function () {
    $sub = new Subscription;
    $sub->status = 'active';
    $sub->starts_at = now()->subDay();
    $sub->ends_at = null;

    expect($sub->isActive())->toBeTrue();
});

it('reads scalar plan limitations', function () {
    $sub = new Subscription;
    $sub->plan_limitations = ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false];

    expect($sub->limitation('max_events', 0))->toBe(3)
        ->and($sub->limitation('max_registrations', 0))->toBe(50)
        ->and($sub->limitation('sending_emails', false))->toBe(false)
        ->and($sub->limitation('missing', 99))->toBe(99);
});

it('treats -1 and "unlimited" as unlimited (null)', function () {
    $sub = new Subscription;
    $sub->plan_limitations = ['max_events' => -1];

    expect($sub->limitation('max_events', 0))->toBeNull()
        ->and($sub->isUnlimited('max_events'))->toBeTrue();

    $sub->plan_limitations = ['max_events' => 'unlimited'];

    expect($sub->limitation('max_events', 0))->toBeNull()
        ->and($sub->isUnlimited('max_events'))->toBeTrue();
});

it('denies site access when there is no active subscription', function () {
    $tenant = Tenant::make(['id' => 'tenant-without-sub']);

    $result = SubscriptionService::canWithReason($tenant, 'access-site');

    expect($result['allowed'])->toBeFalse();
});

it('denies site access when the subscription has expired', function () {
    $plan = Plan::create([
        'name' => 'Free Plan', 'slug' => 'free-plan', 'price' => 0, 'currency' => 'gbp',
        'interval' => 'month', 'interval_count' => 1,
        'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $tenant = Tenant::query()->insert([
        'id' => 'tenant-expired',
        'user_id' => null,
        'name' => 'Expired Tenant',
        'email' => 'expired@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Subscription::create([
        'tenant_id' => 'tenant-expired',
        'plan_id' => $plan->id,
        'starts_at' => now()->subDays(60),
        'ends_at' => now()->subDay(),
        'status' => 'active',
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::canWithReason(Tenant::make(['id' => 'tenant-expired']), 'access-site');

    expect($result['allowed'])->toBeFalse()
        ->and($result['reason'])->toContain('no longer active');
});

it('allows site access for an active, non-expired subscription', function () {
    $plan = Plan::query()->firstOrCreate([
        'slug' => 'free-plan',
    ], [
        'name' => 'Free Plan', 'slug' => 'free-plan', 'price' => 0, 'currency' => 'gbp',
        'interval' => 'month', 'interval_count' => 1,
        'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    Tenant::query()->insert([
        'id' => 'tenant-ok',
        'user_id' => null,
        'name' => 'Ok Tenant',
        'email' => 'ok@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Subscription::create([
        'tenant_id' => 'tenant-ok',
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(30),
        'status' => 'active',
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::canWithReason(Tenant::make(['id' => 'tenant-ok']), 'access-site');

    expect($result['allowed'])->toBeTrue();
});

it('reports max_events and max_registrations from the plan limitations', function () {
    $plan = Plan::query()->firstOrCreate([
        'slug' => 'pro-plan',
    ], [
        'name' => 'Pro Plan', 'slug' => 'pro-plan', 'price' => 9.99, 'currency' => 'gbp',
        'interval' => 'month', 'interval_count' => 1,
        'limitations' => ['max_events' => 100, 'max_registrations' => 500, 'sending_emails' => true],
    ]);

    Tenant::query()->insert([
        'id' => 'tenant-plan',
        'user_id' => null,
        'name' => 'Plan Tenant',
        'email' => 'plan@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Subscription::create([
        'tenant_id' => 'tenant-plan',
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(30),
        'status' => 'active',
        'plan_limitations' => ['max_events' => 100, 'max_registrations' => 500, 'sending_emails' => true],
    ]);

    $tenant = Tenant::make(['id' => 'tenant-plan']);
    $service = new SubscriptionService;

    expect($service->maxEvents($tenant))->toBe(100)
        ->and($service->maxRegistrations($tenant))->toBe(500)
        ->and($service->canSendEmails($tenant))->toBeTrue();
});

it('denies event creation when there is no active subscription', function () {
    $tenant = Tenant::make(['id' => 'tenant-create-no-sub']);

    $result = SubscriptionService::canWithReason($tenant, 'create-event');

    expect($result['allowed'])->toBeFalse();
});

it('cancels an active subscription and keeps access until ends_at', function () {
    $plan = Plan::query()->firstOrCreate(
        ['slug' => 'free-plan-cancel'],
        [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
        ]
    );

    Tenant::query()->insert([
        'id' => 'tenant-cancel',
        'user_id' => null,
        'name' => 'Cancel Tenant',
        'email' => 'cancel@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sub = Subscription::create([
        'tenant_id' => 'tenant-cancel',
        'plan_id' => $plan->id,
        'plan_name' => 'Free',
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(15),
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::cancelSubscription(Tenant::make(['id' => 'tenant-cancel']));

    expect($result['allowed'])->toBeTrue()
        ->and($result['message'])->toContain('cancelled')
        ->and($sub->fresh()->status)->toBe('cancelled')
        ->and($sub->fresh()->isInGracePeriod())->toBeTrue()
        ->and($sub->fresh()->isActive())->toBeTrue();
});

it('still allows site access during the cancellation grace period', function () {
    $plan = Plan::query()->firstOrCreate(
        ['slug' => 'free-plan-grace'],
        [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
        ]
    );

    Tenant::query()->insert([
        'id' => 'tenant-grace',
        'user_id' => null,
        'name' => 'Grace Tenant',
        'email' => 'grace@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Subscription::create([
        'tenant_id' => 'tenant-grace',
        'plan_id' => $plan->id,
        'plan_name' => 'Free',
        'status' => 'cancelled',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(10),
        'cancellation_date' => now(),
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::canWithReason(Tenant::make(['id' => 'tenant-grace']), 'access-site');

    expect($result['allowed'])->toBeTrue()
        ->and($result['grace_period'] ?? false)->toBeTrue();
});

it('denies site access when the cancellation grace period has ended', function () {
    $plan = Plan::query()->firstOrCreate(
        ['slug' => 'free-plan-grace-ended'],
        [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
        ]
    );

    Tenant::query()->insert([
        'id' => 'tenant-grace-ended',
        'user_id' => null,
        'name' => 'Grace Ended',
        'email' => 'grace-ended@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Subscription::create([
        'tenant_id' => 'tenant-grace-ended',
        'plan_id' => $plan->id,
        'plan_name' => 'Free',
        'status' => 'cancelled',
        'starts_at' => now()->subDays(20),
        'ends_at' => now()->subDay(),
        'cancellation_date' => now()->subDays(5),
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::canWithReason(Tenant::make(['id' => 'tenant-grace-ended']), 'access-site');

    expect($result['allowed'])->toBeFalse();
});

it('reactivates a cancelled subscription within the grace period', function () {
    $plan = Plan::query()->firstOrCreate(
        ['slug' => 'free-plan-reactivate'],
        [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
        ]
    );

    Tenant::query()->insert([
        'id' => 'tenant-reactivate',
        'user_id' => null,
        'name' => 'Reactivate Tenant',
        'email' => 'reactivate@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sub = Subscription::create([
        'tenant_id' => 'tenant-reactivate',
        'plan_id' => $plan->id,
        'plan_name' => 'Free',
        'status' => 'cancelled',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(10),
        'cancellation_date' => now(),
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::reactivateSubscription(Tenant::make(['id' => 'tenant-reactivate']));

    expect($result['allowed'])->toBeTrue()
        ->and($sub->fresh()->status)->toBe('active')
        ->and($sub->fresh()->cancellation_date)->toBeNull();
});

it('does not cancel an already cancelled subscription twice', function () {
    $plan = Plan::query()->firstOrCreate(
        ['slug' => 'free-plan-double-cancel'],
        [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
        ]
    );

    Tenant::query()->insert([
        'id' => 'tenant-double-cancel',
        'user_id' => null,
        'name' => 'Double Cancel',
        'email' => 'double-cancel@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Subscription::create([
        'tenant_id' => 'tenant-double-cancel',
        'plan_id' => $plan->id,
        'plan_name' => 'Free',
        'status' => 'cancelled',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(5),
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::cancelSubscription(Tenant::make(['id' => 'tenant-double-cancel']));

    expect($result['allowed'])->toBeTrue()
        ->and($result['message'])->toContain('already cancelled');
});
