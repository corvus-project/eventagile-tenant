<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('does not expose the create page to guests', function () {
    $this->get('/dashboard/events/create')->assertRedirect();
});

it('denies event creation when the tenant has no active subscription', function () {
    $tenant = Tenant::make(['id' => 'no-sub-tenant']);

    $result = SubscriptionService::canWithReason($tenant, 'create-event');

    expect($result['allowed'])->toBeFalse()
        ->and($result['reason'])->toContain('not active');
});

it('denies event creation when the subscription has expired', function () {
    $plan = Plan::create([
        'name' => 'Free Plan', 'slug' => 'free-plan', 'price' => 0, 'currency' => 'gbp',
        'interval' => 'month', 'interval_count' => 1,
        'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    DB::table('tenants')->insert([
        'id' => 'expired-tenant',
        'user_id' => null,
        'name' => 'Expired',
        'email' => 'expired@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Subscription::create([
        'tenant_id' => 'expired-tenant',
        'plan_id' => $plan->id,
        'starts_at' => now()->subDays(60),
        'ends_at' => now()->subDay(),
        'status' => 'active',
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    $result = SubscriptionService::canWithReason(Tenant::make(['id' => 'expired-tenant']), 'create-event');

    expect($result['allowed'])->toBeFalse();
});
