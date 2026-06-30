<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $startTime = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $registrationDeadline = $this->faker->dateTimeBetween('now', $startTime);
        return [
            'title' => $this->faker->randomElement(['Toga for Beginners', 'Advanced Toga Techniques', 'Toga Party Planning', 'Toga History and Culture']),
            'description' => $this->faker->paragraph,
            'full_description' => $this->faker->paragraphs(4),
            //'slug' => $this->faker->unique()->slug,
            'start_time' => $startTime,
            'registration_deadline' => $registrationDeadline,

            'location' => $this->faker->randomElement(['Studio', 'Main Hall', 'Outdoor Venue', 'Virtual']),
            'organizer' => $this->faker->name,
            'capacity' => $this->faker->numberBetween(10, 100),
            'is_public' => $this->faker->boolean(80), // 80%
            'status' => fake()->randomElement(EventStatus::cases())->value,
            'organizer_id' => \App\Models\User::factory(), // Assuming organizer is a User
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
