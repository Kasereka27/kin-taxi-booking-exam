<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'user_id' => User::factory(),
            'method' => fake()->randomElement(['mpesa', 'airtel', 'orange', 'cash', 'card']),
            'provider_reference' => strtoupper(fake()->bothify('TXN-########')),
            'amount' => fake()->numberBetween(5000, 80000),
            'status' => 'success',
            'receipt_path' => null,
            'paid_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
        ]);
    }
}
