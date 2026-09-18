<?php

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Policies\EventPolicy;
use App\Policies\EventRegistrationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RolesTableSeeder']);
});

it('allows an admin to create and update dashboard events', function () {
    $admin = User::factory()->create();
    $admin->attachRole(config('roles.models.role')::where('slug', 'admin')->firstOrFail());
    $event = new Event(['organizer_id' => $admin->id]);

    $policy = app(EventPolicy::class);

    expect($policy->create($admin))->toBeTrue()
        ->and($policy->update($admin, $event))->toBeTrue();
});

it('does not allow a regular user to manage dashboard events', function () {
    $user = User::factory()->create();
    $event = new Event(['organizer_id' => $user->id]);

    $policy = app(EventPolicy::class);

    expect($policy->create($user))->toBeFalse()
        ->and($policy->update($user, $event))->toBeTrue();
});

it('allows only the registered user or an admin to update a registration', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $registration = new EventRegistration(['user_id' => $owner->id]);
    $policy = app(EventRegistrationPolicy::class);

    expect($policy->update($owner, $registration))->toBeTrue()
        ->and($policy->update($otherUser, $registration))->toBeFalse();
});
