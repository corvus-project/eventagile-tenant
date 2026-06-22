<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'user_id' => \App\Models\User::factory(),
            'is_attending' => $this->faker->boolean,
            'registered_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'status' => fake()->randomElement(RegistrationStatus::cases())->value,
            'notes' => $this->faker->optional()->text,
        ];
    }
}
