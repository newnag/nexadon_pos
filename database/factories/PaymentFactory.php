<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
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
            'order_id' => \App\Models\Order::factory(),
            'payment_method' => fake()->randomElement(['Cash', 'Credit Card', 'Debit Card', 'QR Payment']),
            'amount' => fake()->randomFloat(2, 50, 1000),
        ];
    }
}
