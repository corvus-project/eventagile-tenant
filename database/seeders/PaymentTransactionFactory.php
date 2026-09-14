<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        $statuses = ['pending', 'succeeded', 'failed', 'retrying', 'permanently_failed'];
        $status = $this->faker->randomElement($statuses);

        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'subscription_id' => \App\Models\Subscription::factory(),
            'transaction_id' => 'txn_' . $this->faker->uuid(),
            'type' => $this->faker->randomElement(['charge', 'refund', 'renewal']),
            'amount' => $this->faker->randomFloat(2, 9.99, 999.99),
            'currency' => $this->faker->randomElement(['usd', 'eur', 'gbp']),
            'status' => $status,
            'gateway' => 'fake',
            'payment_method' => $this->faker->optional(0.7)->lexify('card_XXXX'),
            'response' => ['raw' => $this->faker->json()],
            'metadata' => ['key' => 'value'],
            'failure_code' => $status === 'failed' || $status === 'permanently_failed' ? $this->faker->randomElement(['card_declined', 'insufficient_funds', 'gateway_timeout']) : null,
            'failure_message' => $status === 'failed' || $status === 'permanently_failed' ? 'The payment was declined.' : null,
            'retry_count' => $status === 'retrying' ? $this->faker->numberBetween(1, 2) : 0,
            'next_retry_at' => $status === 'retrying' ? $this->faker->dateTimeBetween('now', '+1 hour') : null,
        ];
    }
}
