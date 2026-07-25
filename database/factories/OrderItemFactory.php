<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(str_replace('-', ' ', fake()->unique()->slug(3)));

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => ['en' => $name, 'es' => $name],
            'unit_price_cents' => fake()->numberBetween(900, 19900),
            'quantity' => fake()->numberBetween(1, 3),
            'image_path' => null,
        ];
    }
}
