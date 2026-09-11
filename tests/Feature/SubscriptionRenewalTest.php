<?php

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-01-01 00:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function createTenantAndPlan(): array
{
    $tenant = Tenant::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Test Tenant',
        'email' => 'tenant@example.com',
        'is_active' => true,
    ]);

    $plan = Plan::create([
        'name' => 'Monthly Plan',
        'slug' => 'monthly-plan',
        'description' => 'Test plan',
        'price' => 10.00,
        'currency' => 'gbp',
        'interval' => PlanInterval::MONTH->value,
        'interval_count' => 1,
        'features' => ['Access to all features'],
        'limitations' => ['max_events' => 100],
        'is_active' => true,
    ]);

    return [$tenant, $plan];
}

function createSubscription(Tenant $tenant, Plan $plan, array $attributes = []): Subscription
{
    return Subscription::create(array_merge([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'starts_at' => '2025-12-01 00:00:00',
        'ends_at' => '2025-12-31 23:59:59',
        'status' => 'active',
        'interval' => PlanInterval::MONTH->value,
        'interval_count' => 1,
        'plan_limitations' => $plan->limitations,
        'plan_features' => $plan->features,
        'plan_name' => $plan->name,
        'plan_description' => $plan->description,
    ], $attributes));
}

it('renews an expired active subscription by cloning it with extended dates', function () {
    [$tenant, $plan] = createTenantAndPlan();
    $subscription = createSubscription($tenant, $plan);

    expect($subscription->renew())->toBeTrue();

    $subscription->refresh();

    expect($subscription->status)->toBe('expired')
        ->and($subscription->renewal_status)->toBe('completed')
        ->and($subscription->renewal_date)->not->toBeNull();

    $renewed = Subscription::query()
        ->where('tenant_id', $tenant->id)
        ->where('status', 'active')
        ->first();

    expect($renewed)->not->toBeNull()
        ->and($renewed->starts_at->equalTo($subscription->ends_at))->toBeTrue()
        ->and($renewed->ends_at->equalTo(Carbon::parse('2026-01-31 23:59:59')))->toBeTrue()
        ->and($renewed->plan_id)->toBe($plan->id)
        ->and($renewed->plan_limitations)->toBeArray()
        ->and($renewed->renewal_status)->toBeNull()
        ->and($renewed->renewal_date)->toBeNull();
});

it('does not renew a non-active subscription', function () {
    [$tenant, $plan] = createTenantAndPlan();
    createSubscription($tenant, $plan, [
        'status' => 'cancelled',
    ]);

    $subscription = Subscription::first();

    expect($subscription->renew())->toBeFalse()
        ->and(Subscription::count())->toBe(1);
});

it('does not renew a subscription that has not ended yet', function () {
    [$tenant, $plan] = createTenantAndPlan();
    createSubscription($tenant, $plan, [
        'ends_at' => '2026-02-01 00:00:00',
    ]);

    $subscription = Subscription::first();

    expect($subscription->renew())->toBeFalse()
        ->and(Subscription::count())->toBe(1);
});

it('does not create a second clone when renew is called twice', function () {
    [$tenant, $plan] = createTenantAndPlan();
    $subscription = createSubscription($tenant, $plan);

    expect($subscription->renew())->toBeTrue()
        ->and($subscription->renew())->toBeFalse()
        ->and(Subscription::where('tenant_id', $tenant->id)->count())->toBe(2);
});

it('renews all due subscriptions via the command and reports the count', function () {
    [$tenant, $plan] = createTenantAndPlan();
    createSubscription($tenant, $plan);
    createSubscription($tenant, $plan);

    $this->artisan('renew-subscription')
        ->expectsOutput('Renewed 2 subscription(s).')
        ->assertSuccessful();

    expect(Subscription::where('status', 'expired')->count())->toBe(2)
        ->and(Subscription::where('status', 'active')->count())->toBe(2);
});
