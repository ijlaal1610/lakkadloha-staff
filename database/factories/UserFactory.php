<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => fake()->randomElement(['staff', 'manager']),
            'is_active' => true,
            'employee_id' => 'LL' . str_pad(fake()->unique()->numberBetween(100, 9999), 4, '0', STR_PAD_LEFT),
            'joining_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'designation' => fake()->jobTitle(),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'super_admin']);
    }

    public function manager(): static
    {
        return $this->state(['role' => 'manager']);
    }

    public function staff(): static
    {
        return $this->state(['role' => 'staff']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
