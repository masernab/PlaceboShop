<?php

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('????##')),
            'type' => CouponType::Percent,
            'value' => 10,
            'min_subtotal_cents' => 0,
            'max_uses' => null,
            'used_count' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    public function percent(int $percent = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::Percent,
            'value' => $percent,
        ]);
    }

    public function fixed(int $cents = 500): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::Fixed,
            'value' => $cents,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_uses' => 5,
            'used_count' => 5,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
