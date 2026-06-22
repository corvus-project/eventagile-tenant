<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;

class DemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenant = Tenant::where('email', 'acme@example.com')->first();
        $tenant->run(function () {
            EventRegistration::truncate();
            Event::truncate();
            User::truncate();

            $users = User::factory()->user()->count(100)->create();

            $events = Event::factory()
                ->count(15)
                ->create();

            EventRegistration::factory(500)
                ->recycle($events)
                ->recycle($users)->create();

            $user = User::factory()->create([
                'name' => 'Test Tenant',
                'email' => 'acme@example.com',
            ]);
            $adminRole = config('roles.models.role')::where('name', '=', 'Admin')->first();
            $user->attachRole($adminRole);
        });
    }
}
