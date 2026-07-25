<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(3);
        $name = Str::title(str_replace('-', ' ', $slug));

        return [
            'category_id' => Category::factory(),
            'slug' => $slug,
            'sku' => strtoupper(fake()->unique()->bothify('PB-????-####')),
            'name' => ['en' => $name, 'es' => $name],
            'description' => [
                'en' => fake()->sentence(12),
                'es' => fake()->sentence(12),
            ],
            'price_cents' => fake()->numberBetween(900, 19900),
            'compare_at_price_cents' => null,
            'stock' => fake()->numberBetween(0, 50),
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    /**
     * Indicate that the product is featured on the home page.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the product is hidden from the shop.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is on sale.
     */
    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'compare_at_price_cents' => $attributes['price_cents'] + fake()->numberBetween(500, 5000),
        ]);
    }
}
