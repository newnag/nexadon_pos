<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Admin', 'Manager', 'Cashier', 'Waiter']),
        ];
    }
    
    /**
     * Indicate that the role is Admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => 1,
            'name' => 'Admin',
        ]);
    }
    
    /**
     * Indicate that the role is Manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => 2,
            'name' => 'Manager',
        ]);
    }
    
    /**
     * Indicate that the role is Cashier.
     */
    public function cashier(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => 3,
            'name' => 'Cashier',
        ]);
    }
    
    /**
     * Indicate that the role is Waiter.
     */
    public function waiter(): static
    {
        return $this->state(fn (array $attributes) => [
            'id' => 4,
            'name' => 'Waiter',
        ]);
    }
}
