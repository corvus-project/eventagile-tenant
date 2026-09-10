<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionService
{
    protected ?Subscription $subscription = null;

    public static function can(Tenant $tenant, string $action): bool
    {
        return self::canWithReason($tenant, $action)['allowed'];
    }

    /**
     * Resolve an action into [allowed, reason].
     * Action keys may be passed as kebab-case (e.g. "create-event")
     * or camelCase (e.g. "createEvent").
     */
    public static function canWithReason(Tenant $tenant, string $action): array
    {
        $action = Str::camel($action);

        return match ($action) {
            'createEvent' => (new self)->createEvent($tenant),
            'sendEmails' => (new self)->sendEmails($tenant),
            'register' => (new self)->canRegister($tenant),
            'accessSite' => (new self)->accessSite($tenant),
            default => [
                'allowed' => false,
                'reason' => "Unsupported subscription action [{$action}].",
            ],
        };
    }

    private function accessSite(Tenant $tenant): array
    {
        $sub = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();

        if (! $sub) {
            return ['allowed' => false, 'reason' => 'No active subscription found for your account.'];
        }

        if ($sub->isActive()) {
            if ($sub->isInGracePeriod()) {
                return [
                    'allowed' => true,
                    'reason' => '',
                    'grace_period' => true,
                    'ends_at' => $sub->ends_at,
                ];
            }

            return ['allowed' => true, 'reason' => ''];
        }

        if ($sub->status !== 'active' && $sub->status !== 'cancelled') {
            return [
                'allowed' => false,
                'reason' => 'Your subscription is no longer active (status: ' . $sub->status . ').',
            ];
        }

        return [
            'allowed' => false,
            'reason' => 'Your subscription is no longer active or has expired.',
        ];
    }

    /**
     * Cancel a subscription. The user retains access until the current
     * ends_at. If no ends_at is set, it defaults to one month from now.
     *
     * Returns ['allowed' => bool, 'message' => string, 'ends_at' => ?Carbon].
     */
    public static function cancelSubscription(Tenant $tenant, ?Subscription $subscription = null, ?int $requestedByUserId = null): array
    {
        $subscription ??= (new self)->getActiveSubscription($tenant);

        if (! $subscription) {
            $subscription = Subscription::query()
                ->where('tenant_id', $tenant->id)
                ->latest('starts_at')
                ->first();
        }

        if (! $subscription) {
            return [
                'allowed' => false,
                'message' => 'No subscription found to cancel.',
            ];
        }

        if ($subscription->status === 'cancelled') {
            return [
                'allowed' => true,
                'message' => 'Subscription is already cancelled. You can continue using the system until ' . optional($subscription->ends_at)->format('F j, Y') . '.',
                'ends_at' => $subscription->ends_at,
            ];
        }

        if ($subscription->status !== 'active') {
            return [
                'allowed' => false,
                'message' => 'Only active subscriptions can be cancelled. Current status: ' . $subscription->status . '.',
            ];
        }

        $endsAt = $subscription->ends_at;
        if (! $endsAt) {
            $endsAt = now()->addMonth();
        }

        $subscription->status = 'cancelled';
        $subscription->ends_at = $endsAt;
        $subscription->cancellation_date = now();
        if ($requestedByUserId) {
            $subscription->cancellation_requested_by = $requestedByUserId;
        }
        $subscription->save();

        Log::info('Subscription cancelled', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'ends_at' => $endsAt->toIso8601String(),
        ]);

        return [
            'allowed' => true,
            'message' => 'Your subscription has been cancelled. You can continue using the system until ' . $endsAt->format('F j, Y') . '.',
            'ends_at' => $endsAt,
        ];
    }

    /**
     * Reactivate a previously cancelled subscription if it's still within
     * the grace period.
     */
    public static function reactivateSubscription(Tenant $tenant, ?Subscription $subscription = null): array
    {
        $subscription ??= Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'cancelled')
            ->latest('starts_at')
            ->first();

        if (! $subscription) {
            return [
                'allowed' => false,
                'message' => 'No cancelled subscription found to reactivate.',
            ];
        }

        if ($subscription->ends_at && now()->gt($subscription->ends_at)) {
            return [
                'allowed' => false,
                'message' => 'The cancellation grace period has already ended.',
            ];
        }

        $subscription->status = 'active';
        $subscription->cancellation_date = null;
        $subscription->cancellation_requested_by = null;
        $subscription->save();

        return [
            'allowed' => true,
            'message' => 'Your subscription has been reactivated.',
        ];
    }

    private function createEvent(Tenant $tenant): array
    {
        $sub = $this->getActiveSubscription($tenant);

        if (! $sub || ! $sub->isActive()) {
            return ['allowed' => false, 'reason' => 'Your subscription is not active.'];
        }

        $limit = $this->maxEvents($tenant);

        if ($limit === null) {
            return ['allowed' => true, 'reason' => ''];
        }

        $count = Event::query()->count();

        if ($count >= $limit) {
            Log::warning('Tenant ID: ' . $tenant->id . ' reached the max events limit (' . $limit . ').');

            return [
                'allowed' => false,
                'reason' => 'You have reached the maximum number of events (' . $limit . ') allowed by your plan.',
            ];
        }

        return ['allowed' => true, 'reason' => ''];
    }

    private function canRegister(Tenant $tenant): array
    {
        $sub = $this->getActiveSubscription($tenant);

        if (! $sub || ! $sub->isActive()) {
            return ['allowed' => false, 'reason' => 'Your subscription is not active.'];
        }

        $limit = $this->maxRegistrations($tenant);

        if ($limit === null) {
            return ['allowed' => true, 'reason' => ''];
        }

        $total = $this->registrationsTotal($tenant);

        if ($total >= $limit) {
            Log::warning('Tenant ID: ' . $tenant->id . ' reached the max registrations limit (' . $limit . '). Total registered: ' . $total);

            return [
                'allowed' => false,
                'reason' => 'The site has reached the maximum number of registrations!',
            ];
        }

        return ['allowed' => true, 'reason' => ''];
    }

    private function sendEmails(Tenant $tenant): array
    {
        $sub = $this->getActiveSubscription($tenant);

        if (! $sub || ! $sub->isActive()) {
            return ['allowed' => false, 'reason' => 'Your subscription is not active.'];
        }

        $allowed = $sub->plan_limitations['sending_emails'] ?? false;

        return [
            'allowed' => (bool) $allowed,
            'reason' => $allowed ? '' : 'Email sending is not enabled on your plan.',
        ];
    }

    public function getActiveSubscription(Tenant $tenant): ?Subscription
    {
        if ($this->subscription) {
            return $this->subscription;
        }

        $this->subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->active()
            ->latest('starts_at')
            ->first();

        if (! $this->subscription) {
            Log::warning('No active subscription found for tenant ID: ' . $tenant->id);
        }

        return $this->subscription;
    }

    public function maxEvents(Tenant $tenant): ?int
    {
        $sub = $this->getActiveSubscription($tenant);

        if (! $sub) {
            return 0;
        }

        $value = $sub->limitation('max_events', 0);

        if (is_null($value)) {
            return null;
        }

        return (int) $value;
    }

    public function maxRegistrations(Tenant $tenant): ?int
    {
        $sub = $this->getActiveSubscription($tenant);

        if (! $sub) {
            return 0;
        }

        $value = $sub->limitation('max_registrations', 0);
        Log::debug('max_registrations: ' . $value);
        if (is_null($value)) {
            return null;
        }

        return (int) $value;
    }

    public static function canSendEmails(Tenant $tenant): bool
    {
        return self::canWithReason($tenant, 'send-emails')['allowed'];
    }

    public function registrationsTotal(Tenant $tenant): int
    {
        return EventRegistration::query()
            ->where('is_attending', true)
            ->count();
    }
}
