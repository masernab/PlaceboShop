<?php

namespace Tests\Feature\Admin;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_coupon_can_be_created_with_normalized_code()
    {
        $this->actingAs($this->admin)->post('/admin/coupons', [
            'code' => '  spring25 ',
            'type' => 'percent',
            'value' => 25,
            'min_subtotal' => '30.00',
            'max_uses' => 100,
            'starts_at' => '2026-08-01',
            'expires_at' => '2026-08-31',
            'is_active' => true,
        ]);

        $coupon = Coupon::query()->sole();
        $this->assertSame('SPRING25', $coupon->code);
        $this->assertSame(CouponType::Percent, $coupon->type);
        $this->assertSame(3000, $coupon->min_subtotal_cents);
        $this->assertSame(100, $coupon->max_uses);
        $this->assertTrue($coupon->is_active);
    }

    public function test_percent_value_cannot_exceed_100()
    {
        $response = $this->actingAs($this->admin)->post('/admin/coupons', [
            'code' => 'TOOMUCH',
            'type' => 'percent',
            'value' => 150,
        ]);

        $response->assertSessionHasErrors('value');
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_duplicate_code_is_rejected()
    {
        Coupon::factory()->create(['code' => 'WELCOME10']);

        $response = $this->actingAs($this->admin)->post('/admin/coupons', [
            'code' => 'welcome10',
            'type' => 'fixed',
            'value' => 500,
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertDatabaseCount('coupons', 1);
    }

    public function test_coupon_can_be_updated_keeping_its_own_code()
    {
        $coupon = Coupon::factory()->create(['code' => 'KEEPME']);

        $this->actingAs($this->admin)->put("/admin/coupons/{$coupon->id}", [
            'code' => 'KEEPME',
            'type' => 'fixed',
            'value' => 750,
            'is_active' => false,
        ]);

        $coupon->refresh();
        $this->assertSame(CouponType::Fixed, $coupon->type);
        $this->assertSame(750, $coupon->value);
        $this->assertFalse($coupon->is_active);
    }

    public function test_coupon_can_be_deleted()
    {
        $coupon = Coupon::factory()->create();

        $this->actingAs($this->admin)->delete("/admin/coupons/{$coupon->id}");

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }
}
