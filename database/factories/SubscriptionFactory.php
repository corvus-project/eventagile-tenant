<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = \App\Models\Plan::inRandomOrder()->first();
        return [
            'user_id' => \App\Models\User::factory(),
            'starts_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'ends_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'cancelled']),
            'plan_id' => $plan->id,
            'interval' => $plan->interval,
            'plan_limitations' => $plan->limitations,
            'plan_features' => $plan->features,
            'plan_name' => $plan->name,
            'plan_description' => $plan->description,
        ];
    }
}
