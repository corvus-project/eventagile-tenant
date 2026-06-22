<?php

namespace App\Listeners;

use App\Models\Tenant;
use App\Services\OnBoardingService;
use Illuminate\Auth\Events\Verified as EventsVerified;
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
    public function handle(EventsVerified $event): void
    {
        Log::info('User email verified', ['user_id' => $event->user->id, $event]);
        $tenant = Tenant::where('email', $event->user->email)->first();
        if ($tenant) {
            $tenant->is_active = true;
            $tenant->save();
            Log::info('Tenant activated', ['tenant_id' => $tenant->id]);

            (new OnBoardingService())->process($tenant, [
                'admin_name' => $event->user->name,
                'admin_email' => $event->user->email,
                'admin_password' => 'password', // In real application, generate a secure password and send to user
            ]);
        } else {
            Log::warning('No tenant found for verified user', ['user_id' => $event->user->id]);
        }
    }
}
