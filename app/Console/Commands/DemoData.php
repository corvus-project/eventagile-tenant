<?php

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Setting;
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
        if ($tenant) {
            $this->info('Data will refresh');
            $tenant->run(function () {
                EventRegistration::truncate();
                Event::truncate();
                User::truncate();
                Setting::truncate();

                Setting::create([
                    'name' => 'site_name',
                    'payload' => 'Acme Toga Studio'
                ]);

                Setting::create([
                    'name' => 'site_slogan',
                    'payload' => 'Find Your Inner Peace with Toga'
                ]);
                Setting::create([
                    'name' => 'site_description',
                    'payload' => 'Join Acme\'s yoga classes and transform your mind, body, and soul. Suitable for all levels.'
                ]);
                Setting::create([
                    'name' => 'contact',
                    'payload' => 'Westgate Brewery \nBury St Edmunds \nSuffolk \n IP33 1QT'
                ]);
                Setting::create([
                    'name' => 'about',
                    'payload' => ''
                ]);

                $users = User::factory()->user()->count(100)->create();

                $events = Event::factory()
                    ->count(15)
                    ->create();


                $events = Event::factory()
                    ->count(5)
                    ->create(
                        [
                            'start_time' => Now()->addDays(1),
                            'status' => EventStatus::SCHEDULED->value,
                        ]
                    );

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
            $this->info('Fresh sample data is populated!');
        }
    }
}
