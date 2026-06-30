<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OnBoardingService
{
    public function process(Tenant $tenant, array $tenantData)
    {
        $tenant->run(function ($tenant) use ($tenantData) {
            $this->createRoles($tenant);
            $this->createAdminUser($tenant, [
                'name' => $tenantData['admin_name'],
                'email' => $tenantData['admin_email'],
                'password' => $tenantData['admin_password'],
            ]);
            $this->createSettingsTable();
        });
        $this->createSubscriptions($tenant);
    }

    private function createAdminUser(Tenant $tenant, array $adminData)
    {
        $tenant->run(function () use ($adminData) {
            $user = User::create([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => bcrypt($adminData['password']),
                'email_verified_at' => now(),
            ]);
            Log::info('Tenant admin user is created: ', [
                'id' => $user->id,
                'email' => $user->email
            ]);
            // Assign admin role to the user
            $role = config('roles.models.role')::where('name', '=', 'Admin')->first();
            $user->attachRole($role);
            $user->email_verified_at = now();
            $user->save();
        });
    }

    private function createRoles(Tenant $tenant)
    {
        $tenant->run(function ($tenant) {
            $roles = [
                ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Admin Role', 'level' => 5],
                ['name' => 'User', 'slug' => 'user', 'description' => 'User Role', 'level' => 1],
                ['name' => 'Unverified', 'slug' => 'unverified', 'description' => 'Unverified Role', 'level' => 0],
            ];

            foreach ($roles as $roleData) {
                $existingRole = config('roles.models.role')::where('slug', '=', $roleData['slug'])->first();
                if (!$existingRole) {
                    config('roles.models.role')::create($roleData);
                }
            }
        });
    }

    private function createSubscriptions(Tenant $tenant)
    {
        // Create default subscription for the tenant
        $plan = Plan::where('slug', 'free-plan')->first();
        $tenant->subscriptions()->create([
            'starts_at' => now(),
            'ends_at' => Carbon::now()->addYear(),
            'status' => 'active',
            'plan_id' => $plan->id,
            'interval' => $plan->interval,
            'plan_limitations' => $plan->limitations,
            'plan_features' => $plan->features,
            'plan_name' => $plan->name,
            'plan_description' => $plan->description,
        ]);
    }

    private function createSettingsTable()
    {
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
    }
}
