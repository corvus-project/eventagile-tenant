<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'fake'),
    'gateways' => [
        'fake' => App\Services\FakePaymentGateway::class,
        'stripe' => App\Services\StripePaymentGateway::class,
    ],
    'fail_rate' => (float) env('APP_PAYMENT_FAIL_RATE', 0),
    'currency' => env('PAYMENT_CURRENCY', 'usd'),
    'maintenance_mode' => (bool) env('PAYMENT_MAINTENANCE_MODE', false),
    'max_retry_attempts' => 3,
    'retry_intervals' => [300, 900, 2700],
    'trial_days' => (int) env('PAYMENT_TRIAL_DAYS', 15),
];
