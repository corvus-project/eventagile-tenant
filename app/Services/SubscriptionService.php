<?php


namespace App\Services;

use App\Models\Event;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionService
{
    protected  $subscription;

    public function __construct()
    {
        $this->subscription = null;
    }

    public static function can(Tenant $tenant, string $action): bool
    {
        $action = Str::camel($action);
        return (new self)->$action($tenant);
    }

    private function createEvent(Tenant $tenant): bool
    {
        Log::debug('Checking create-event ' . Event::count() . ' events, max allowed: ' . $this->getMaxEvents($tenant));
        if (Event::count() >= $this->getMaxEvents($tenant)) {
            Log::warning('Tenant ID: ' . $tenant->id . ' has reached the maximum number of events allowed by their subscription.');
            return false;
        }
        return true;
    }

    private function getMaxEvents(Tenant $tenant): int
    {
        $subscription = Subscription::on('central_connection')->where('tenant_id', $tenant->id)->where('status', 'active')->firstOrFail();
        if ($subscription) {
            $plan_limitations = is_array($subscription->plan_limitations) ? $subscription->plan_limitations : json_decode($subscription->plan_limitations, true);
            return $plan_limitations['max_events'] ?? 0;
        }
        Log::warning('No active subscription found for tenant ID: ' . $tenant->id);
        return 10;
    }

    public function getRegistrationLimit(Tenant $tenant): int
    {
        $subscription = Subscription::on('central_connection')->where('tenant_id', $tenant->id)->where('status', 'active')->firstOrFail();
        if ($subscription) {
            $plan_limitations = json_decode($subscription->plan_limitations, true);
            return $plan_limitations['max_registrations'] ?? 0;
        }
        Log::warning('No active subscription found for tenant ID: ' . $tenant->id);
        return 0;
    }
}
