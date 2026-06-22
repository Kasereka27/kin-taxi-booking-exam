<?php

namespace Database\Factories;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverProfile>
 */
class DriverProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->driver(),
            'vehicle_model' => fake()->randomElement(['Toyota Corolla', 'Hyundai Accent', 'Toyota Hiace', 'Suzuki Swift', 'Kia Sportage']),
            'plate' => strtoupper(fake()->bothify('??-####')),
            'vehicle_type' => fake()->randomElement(['eco', 'confort', 'van']),
            'license_document_path' => null,
            'rating' => fake()->randomFloat(2, 3.5, 5),
            'is_online' => fake()->boolean(60),
            'current_lat' => fake()->latitude(-4.5, -4.3),
            'current_lng' => fake()->longitude(15.2, 15.4),
            'approval_status' => 'approved',
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_online' => true,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
        ]);
    }
}
