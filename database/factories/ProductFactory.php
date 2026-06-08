<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'current_stock' => fake()->numberBetween(0, 500),
            'low_stock_threshold' => fake()->numberBetween(5, 20),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'current_stock' => fake()->numberBetween(1, 9),
            'low_stock_threshold' => 10,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(['current_stock' => 0]);
    }
}
