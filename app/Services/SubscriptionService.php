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
    protected $subscription;

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
        $event_counts = Event::where('status', 'active')->count();
        Log::debug('Checking create-event ' . $event_counts . ' events, max allowed: ' . $this->getMaxEvents($tenant));
        if ($event_counts >= $this->getMaxEvents($tenant)) {
            Log::warning('Tenant ID: ' . $tenant->id . ' has reached the maximum number of events allowed by their subscription.');
            return false;
        }
        return true;
    }

    private function getMaxEvents(Tenant $tenant): int
    {
        $subscription = $this->getActiveSubscription($tenant);
        if ($subscription) {
            $plan_limitations = is_array($subscription->plan_limitations) ? $subscription->plan_limitations : json_decode($subscription->plan_limitations, true);
            return $plan_limitations['max_events'] ?? 0;
        }
        Log::warning('No active subscription found for tenant ID: ' . $tenant->id);
        return 10;
    }

    public function getRegistrationLimit(Tenant $tenant): int
    {
        $subscription = $this->getActiveSubscription($tenant);
        if ($subscription) {
            $plan_limitations = json_decode($subscription->plan_limitations, true);
            return $plan_limitations['max_registrations'] ?? 0;
        }
        Log::warning('No active subscription found for tenant ID: ' . $tenant->id);
        return 0;
    }

    public function getActiveSubscription(Tenant $tenant): ?Subscription
    {
        if ($this->subscription) {
            return $this->subscription;
        }

        $this->subscription = Subscription::on('central_connection')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->first();

        if (!$this->subscription) {
            Log::warning('No active subscription found for tenant ID: ' . $tenant->id);
        }

        return $this->subscription;
    }
}
