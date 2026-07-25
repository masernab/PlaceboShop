<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2000, 30000);
        $shipping = $subtotal >= 5000 ? 0 : 499;

        return [
            'user_id' => User::factory(),
            'order_number' => sprintf('PB-%d-%s', now()->year, strtoupper(Str::random(6))),
            'placed_at' => now(),
            'cancelled_at' => null,
            'subtotal_cents' => $subtotal,
            'discount_cents' => 0,
            'shipping_cents' => $shipping,
            'total_cents' => $subtotal + $shipping,
            'coupon_id' => null,
            'coupon_code' => null,
            'card_brand' => 'visa',
            'card_last4' => '4242',
            'tracking_number' => 'PBX'.fake()->numerify('##########'),
            'ship_name' => fake()->name(),
            'ship_line1' => fake()->streetAddress(),
            'ship_line2' => null,
            'ship_city' => fake()->city(),
            'ship_postal_code' => fake()->postcode(),
            'ship_country' => 'US',
        ];
    }

    /**
     * Indicate that the order was placed at the given moment.
     */
    public function placedAt(CarbonInterface $placedAt): static
    {
        return $this->state(fn (array $attributes) => [
            'placed_at' => $placedAt,
        ]);
    }

    /**
     * Indicate that the order was cancelled shortly after being placed.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'cancelled_at' => Carbon::parse($attributes['placed_at'])->addMinutes(5),
        ]);
    }
}
