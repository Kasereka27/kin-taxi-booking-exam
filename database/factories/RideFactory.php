<?php

namespace Database\Factories;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ride>
 */
class RideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $distance = fake()->randomFloat(2, 2, 25);
        $vehicleType = fake()->randomElement(['eco', 'confort', 'van']);

        $requestedAt = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'client_id' => User::factory(),
            'driver_id' => User::factory()->driver(),
            'pickup_addr' => fake()->streetAddress().', Kinshasa',
            'pickup_lat' => fake()->latitude(-4.5, -4.3),
            'pickup_lng' => fake()->longitude(15.2, 15.4),
            'dropoff_addr' => fake()->streetAddress().', Kinshasa',
            'dropoff_lat' => fake()->latitude(-4.5, -4.3),
            'dropoff_lng' => fake()->longitude(15.2, 15.4),
            'vehicle_type' => $vehicleType,
            'status' => 'completed',
            'price' => Ride::estimatePrice($vehicleType, $distance),
            'distance_km' => $distance,
            'requested_at' => $requestedAt,
            'accepted_at' => (clone $requestedAt)->modify('+3 minutes'),
            'completed_at' => (clone $requestedAt)->modify('+25 minutes'),
            'cancelled_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'driver_id' => null,
            'status' => 'pending',
            'price' => null,
            'accepted_at' => null,
            'completed_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'completed_at' => null,
            'cancelled_at' => now(),
        ]);
    }
}
