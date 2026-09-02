<?php

namespace App\Rules;

use App\Models\Tenant;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Enforces the tenant-wide registration quota (max_registrations)
 * and that the tenant has an active, non-expired subscription.
 *
 * Attach to any non-empty field on a registration form (e.g. user_id).
 */
class CapacityLimit implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $result = SubscriptionService::canWithReason($tenant, 'register');

        if (! $result['allowed']) {
            $fail($result['reason']);
        }
    }
}
