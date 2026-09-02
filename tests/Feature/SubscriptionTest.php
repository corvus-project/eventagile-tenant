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

it('reports a cancelled subscription as inactive even within the window', function () {
    $sub = new Subscription;
    $sub->status = 'canceled';
    $sub->starts_at = now()->subDay();
    $sub->ends_at = now()->addDays(5);

    expect($sub->isActive())->toBeFalse();
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
