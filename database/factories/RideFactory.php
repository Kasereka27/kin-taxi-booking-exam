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
     * Repères Kinshasa cohérents entre adresse texte et coordonnées GPS.
     *
     * @var list<array{pickup_addr: string, pickup_lat: float, pickup_lng: float, dropoff_addr: string, dropoff_lat: float, dropoff_lng: float}>
     */
    private const KINSHASA_ROUTES = [
        [
            'pickup_addr' => 'Gare Centrale, Kinshasa',
            'pickup_lat' => -4.3217,
            'pickup_lng' => 15.3125,
            'dropoff_addr' => 'Aéroport de N\'djili, Kinshasa',
            'dropoff_lat' => -4.3858,
            'dropoff_lng' => 15.4446,
        ],
        [
            'pickup_addr' => 'Palais de la Nation, Kinshasa',
            'pickup_lat' => -4.3276,
            'pickup_lng' => 15.3136,
            'dropoff_addr' => 'Marché Central, Kinshasa',
            'dropoff_lat' => -4.3250,
            'dropoff_lng' => 15.3089,
        ],
        [
            'pickup_addr' => 'Université de Kinshasa, Lemba',
            'pickup_lat' => -4.3381,
            'pickup_lng' => 15.2694,
            'dropoff_addr' => 'Gombe, Kinshasa',
            'dropoff_lat' => -4.3196,
            'dropoff_lng' => 15.2989,
        ],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $route = fake()->randomElement(self::KINSHASA_ROUTES);
        $distance = Ride::distanceKmBetween(
            $route['pickup_lat'],
            $route['pickup_lng'],
            $route['dropoff_lat'],
            $route['dropoff_lng'],
        );
        $vehicleType = fake()->randomElement(['eco', 'confort', 'van']);

        $requestedAt = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'client_id' => User::factory(),
            'driver_id' => User::factory()->driver(),
            'pickup_addr' => $route['pickup_addr'],
            'pickup_lat' => $route['pickup_lat'],
            'pickup_lng' => $route['pickup_lng'],
            'dropoff_addr' => $route['dropoff_addr'],
            'dropoff_lat' => $route['dropoff_lat'],
            'dropoff_lng' => $route['dropoff_lng'],
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
