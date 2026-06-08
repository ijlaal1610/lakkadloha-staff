<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    public function definition(): array
    {
        $qty   = fake()->numberBetween(1, 10);
        $price = fake()->randomFloat(2, 50, 1000);

        return [
            'sale_number'   => 'LL' . fake()->unique()->numerify('########'),
            'product_id'    => Product::factory(),
            'staff_id'      => User::factory()->staff(),
            'quantity'      => $qty,
            'selling_price' => $price,
            'total_amount'  => round($qty * $price, 2),
            'status'        => 'completed',
            'sold_at'       => fake()->dateTimeBetween('-30 days', 'now'),
            'customer_name' => fake()->optional()->name(),
        ];
    }
}
