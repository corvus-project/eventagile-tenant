<?php

namespace App\Listeners;

use App\Events\OnboardTenant;
use App\Models\Tenant;
use App\Services\OnBoardingService;
use Illuminate\Support\Facades\Log;

class OnboardingUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OnboardTenant $event): void
    {
        $tenant = $event->tenant;
        if ($tenant) {
            $tenant->is_active = true;
            $tenant->save();
            Log::info('Tenant activated', ['tenant_id' => $tenant->id]);

            (new OnBoardingService())->process($tenant, [
                'admin_name' => $tenant->name,
                'admin_email' => $tenant->email,
                'admin_password' => 'password', // In real application, generate a secure password and send to user
            ]);
        } else {
            Log::warning('No tenant found for verified user');
        }
    }
}
