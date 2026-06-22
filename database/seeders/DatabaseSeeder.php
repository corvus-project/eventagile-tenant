<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\EventRegistration;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Tenant;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*         (config('database.default') != 'sqlite') ?? DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Model::unguard();
        User::truncate();

        Plan::truncate();
        Subscription::truncate();
        Model::reguard(); */

        $this->call(PlanSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(ConnectRelationshipsSeeder::class);


        $foo_user = User::factory()->create([
            'name' => 'Foo User',
            'email' => 'foo@example.com',
        ]);
        $role = config('roles.models.role')::where('name', '=', 'User')->first();
        $foo_user->attachRole($role);

        $tenant1 = Tenant::create([
            'id' => 'foo',
            'user_id' => $foo_user->id,
            'name' => 'Foo Tenant',
            'email' => 'foo@example.com',
            'is_active' => true,
        ]);
        $tenant1->domains()->create(['domain' => 'foo.localhost']);


        (config('database.default') != 'sqlite') ?? DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $role = config('roles.models.role')::where('name', '=', 'Admin')->first();
        $user->attachRole($role);
    }
}
