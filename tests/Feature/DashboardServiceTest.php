<?php

use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate', [
        '--path' => database_path('migrations/tenant/2025_07_17_115551_create_events_table.php'),
        '--realpath' => true,
    ]);
    $this->artisan('migrate', [
        '--path' => database_path('migrations/tenant/2025_07_21_092638_create_registrations_table.php'),
        '--realpath' => true,
    ]);
});

it('returns recent events with registration totals without an N plus one query pattern', function () {
    $events = Event::factory()->createMany([
        ['created_at' => now()->subMinute()],
        ['created_at' => now()],
    ]);
    EventRegistration::factory()->create(['event_id' => $events[0]->id]);
    EventRegistration::factory(2)->create(['event_id' => $events[1]->id]);

    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    $recentEvents = app(DashboardService::class)->getRecentEvents();

    expect($recentEvents)->toHaveCount(2)
        ->and($recentEvents[0]['registrations'])->toBe(2)
        ->and($recentEvents[1]['registrations'])->toBe(1)
        ->and($queries)->toBe(1);
});
