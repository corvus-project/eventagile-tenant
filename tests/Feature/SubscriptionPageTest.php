<?php

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Tenant::query()->insert([
        'id' => 'tenant-test-1',
        'user_id' => null,
        'name' => 'Test Tenant',
        'email' => 'test@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Tenant::query()->insert([
        'id' => 'tenant-enterprise',
        'user_id' => null,
        'name' => 'Enterprise Tenant',
        'email' => 'enterprise@example.com',
        'is_active' => true,
        'data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('returns the label for each plan interval', function () {
    expect(PlanInterval::DAY->label())->toBe('day')
        ->and(PlanInterval::WEEK->label())->toBe('week')
        ->and(PlanInterval::MONTH->label())->toBe('month')
        ->and(PlanInterval::YEAR->label())->toBe('year');
});

it('creates a subscription with plan and exposes its limitations', function () {
    $plan = Plan::query()->firstOrCreate([
        'slug' => 'free-plan',
    ], [
        'name' => 'Free Plan',
        'slug' => 'free-plan',
        'description' => 'Free plan',
        'price' => 0,
        'currency' => 'gbp',
        'interval' => 'month',
        'interval_count' => 1,
        'features' => ['Access to basic features'],
        'limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
        'is_active' => true,
    ]);

    $sub = Subscription::create([
        'tenant_id' => 'tenant-test-1',
        'plan_id' => $plan->id,
        'plan_name' => 'Free Plan',
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(30),
        'amount' => 0,
        'currency' => 'gbp',
        'interval' => 'month',
        'interval_count' => 1,
        'plan_limitations' => ['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false],
    ]);

    expect($sub->isActive())->toBeTrue()
        ->and($sub->limitation('max_events'))->toBe(3)
        ->and($sub->isUnlimited('max_events'))->toBeFalse()
        ->and($sub->limitation('sending_emails'))->toBeFalse();
});

it('treats -1 limitations as unlimited on a subscription', function () {
    $plan = Plan::query()->firstOrCreate(
        ['slug' => 'enterprise'],
        [
            'name' => 'Enterprise',
            'price' => 14.99,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'is_active' => true,
        ]
    );

    $sub = Subscription::create([
        'tenant_id' => 'tenant-enterprise',
        'plan_id' => $plan->id,
        'plan_name' => 'Enterprise',
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(30),
        'plan_limitations' => ['max_events' => -1, 'max_registrations' => -1, 'sending_emails' => true],
    ]);

    expect($sub->isUnlimited('max_events'))->toBeTrue()
        ->and($sub->isUnlimited('max_registrations'))->toBeTrue()
        ->and($sub->limitation('sending_emails'))->toBeTrue();
});

it('lists only active plans for the available plans section', function () {
    Plan::query()->firstOrCreate(
        ['slug' => 'free'],
        [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'is_active' => true,
        ]
    );

    Plan::query()->firstOrCreate(
        ['slug' => 'hidden'],
        [
            'name' => 'Hidden',
            'price' => 9.99,
            'currency' => 'gbp',
            'interval' => 'month',
            'interval_count' => 1,
            'is_active' => false,
        ]
    );

    $activePlans = Plan::query()->where('is_active', true)->get();

    expect($activePlans)->toHaveCount(1)
        ->and($activePlans->first()->slug)->toBe('free');
});
