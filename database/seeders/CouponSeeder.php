<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => CouponType::Percent,
                'value' => 10,
            ],
            [
                'code' => 'GLOW20',
                'type' => CouponType::Percent,
                'value' => 20,
                'min_subtotal_cents' => 5000,
            ],
            [
                'code' => 'TREAT5',
                'type' => CouponType::Fixed,
                'value' => 500,
            ],
            [
                'code' => 'LASTSEASON',
                'type' => CouponType::Percent,
                'value' => 15,
                'starts_at' => now()->subMonths(3),
                'expires_at' => now()->subMonth(),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                $coupon,
            );
        }
    }
}
