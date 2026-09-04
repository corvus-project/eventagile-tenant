<?php

use App\Enums\PlanInterval;
use App\Models\Plan;

if (! function_exists('formatPrice')) {
    function formatPrice(?float $price, string $currency = 'GBP'): string
    {
        if ($price === null || $price === 0.0) {
            return 'Free';
        }

        $symbol = match (strtoupper($currency)) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => strtoupper($currency).' ',
        };

        return $symbol.number_format($price, 2);
    }
}

if (! function_exists('formatInterval')) {
    function formatInterval(int $count, string $interval): string
    {
        $unit = PlanInterval::tryFrom($interval)?->label() ?? $interval;

        if ($count === 1) {
            return "per {$unit}";
        }

        return "every {$count} {$unit}s";
    }
}

if (! function_exists('planFeatures')) {
    function planFeatures(?Plan $plan): array
    {
        if (! $plan || ! is_array($plan->features)) {
            return [];
        }

        return $plan->features;
    }
}

if (! function_exists('planLimitations')) {
    function planLimitations(?Plan $plan): array
    {
        if (! $plan || ! is_array($plan->limitations)) {
            return [];
        }

        return $plan->limitations;
    }
}
