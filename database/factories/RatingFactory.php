<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ride_id' => Ride::factory(),
            'from_user_id' => User::factory(),
            'to_user_id' => User::factory()->driver(),
            'stars' => fake()->numberBetween(3, 5),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
