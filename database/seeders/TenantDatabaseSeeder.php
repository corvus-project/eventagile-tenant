<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\EventRegistration;
use App\Models\Event;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Tenant;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        (config('database.default') != 'sqlite') ?? DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Model::unguard();

        echo 'Truncating tables...' . PHP_EOL;

        $this->call(RolesTableSeeder::class);

        echo 'Creating events...' . PHP_EOL;
        $events = Event::factory(10)->create();
        EventRegistration::factory(500)->recycle($events)->create();

        Setting::create([
            'name' => 'name',
            'payload' => 'Tenant Sample Site'
        ]);

        $user = User::factory()->create([
            'name' => 'Test Tenant',
            'email' => 'tenant@example.com',
        ]);
        $role = config('roles.models.role')::where('name', '=', 'Admin')->first();
        $user->attachRole($role);
    }
}
