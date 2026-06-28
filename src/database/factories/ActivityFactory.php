<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->word(),
            'notes' => fake()->sentence(5),
            'location' => fake()->address(),
            'starts_at' => fake()->dateTimeBetween('+1 day', '+2 years'),
        ];
    }
}
