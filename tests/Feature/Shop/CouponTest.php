<?php

namespace Tests\Feature\Shop;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_percent_coupon_discounts_the_subtotal()
    {
        $cart = $this->cartWithSubtotal(2000);
        $coupon = Coupon::factory()->percent(10)->create();

        $response = $this->withSession([
            'cart_id' => $cart->id,
            'coupon_code' => $coupon->code,
        ])->get('/cart');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('totals.discount_cents', 200)
            ->where('totals.total_cents', 2000 - 200 + 499)
            ->where('coupon', $coupon->code)
        );
    }

    public function test_fixed_coupon_is_capped_at_the_subtotal()
    {
        $cart = $this->cartWithSubtotal(300);
        $coupon = Coupon::factory()->fixed(500)->create();

        $response = $this->withSession([
            'cart_id' => $cart->id,
            'coupon_code' => $coupon->code,
        ])->get('/cart');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('totals.discount_cents', 300)
            ->where('totals.total_cents', 499)
        );
    }

    public function test_coupon_can_be_applied_and_removed()
    {
        $cart = $this->cartWithSubtotal(2000);
        $coupon = Coupon::factory()->percent(10)->create(['code' => 'WELCOME10']);

        $this->withSession(['cart_id' => $cart->id])
            ->from('/cart')
            ->post('/cart/coupon', ['code' => 'welcome10']);

        $this->assertSame('WELCOME10', session('coupon_code'));

        $this->withSession([
            'cart_id' => $cart->id,
            'coupon_code' => $coupon->code,
        ])->delete('/cart/coupon');

        $this->assertNull(session('coupon_code'));
    }

    public function test_unknown_coupon_is_rejected()
    {
        $cart = $this->cartWithSubtotal(2000);

        $response = $this->withSession(['cart_id' => $cart->id])
            ->post('/cart/coupon', ['code' => 'NOPE']);

        $response->assertSessionHasErrors(['code' => 'coupon.error_not_found']);
        $this->assertNull(session('coupon_code'));
    }

    public function test_expired_inactive_and_exhausted_coupons_are_rejected()
    {
        $cart = $this->cartWithSubtotal(2000);

        $expired = Coupon::factory()->expired()->create();
        $inactive = Coupon::factory()->inactive()->create();
        $exhausted = Coupon::factory()->exhausted()->create();

        $this->withSession(['cart_id' => $cart->id])
            ->post('/cart/coupon', ['code' => $expired->code])
            ->assertSessionHasErrors(['code' => 'coupon.error_expired']);

        $this->withSession(['cart_id' => $cart->id])
            ->post('/cart/coupon', ['code' => $inactive->code])
            ->assertSessionHasErrors(['code' => 'coupon.error_expired']);

        $this->withSession(['cart_id' => $cart->id])
            ->post('/cart/coupon', ['code' => $exhausted->code])
            ->assertSessionHasErrors(['code' => 'coupon.error_exhausted']);

        $this->assertNull(session('coupon_code'));
    }

    public function test_minimum_subtotal_is_enforced()
    {
        $cart = $this->cartWithSubtotal(2000);
        $coupon = Coupon::factory()->percent(20)->create(['min_subtotal_cents' => 5000]);

        $response = $this->withSession(['cart_id' => $cart->id])
            ->post('/cart/coupon', ['code' => $coupon->code]);

        $response->assertSessionHasErrors(['code' => 'coupon.error_min']);
        $this->assertNull(session('coupon_code'));
    }

    public function test_checkout_snapshots_coupon_and_increments_used_count()
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->userCartWithSubtotal($user, 2000);
        $coupon = Coupon::factory()->percent(10)->create(['used_count' => 3]);

        $response = $this->actingAs($user)
            ->withSession(['coupon_code' => $coupon->code])
            ->post('/checkout', $this->validPayload());

        $order = Order::query()->sole();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(200, $order->discount_cents);
        $this->assertSame(2000 - 200 + 499, $order->total_cents);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame($coupon->code, $order->coupon_code);
        $this->assertSame(4, $coupon->fresh()->used_count);
        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_expiring_between_apply_and_pay_blocks_checkout()
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->userCartWithSubtotal($user, 2000);
        $coupon = Coupon::factory()->percent(10)->create();

        $coupon->update(['expires_at' => now()->subMinute()]);

        $response = $this->actingAs($user)
            ->withSession(['coupon_code' => $coupon->code])
            ->post('/checkout', $this->validPayload());

        $response->assertSessionHasErrors(['coupon' => 'coupon.error_invalid']);
        $this->assertDatabaseCount('orders', 0);
        $this->assertNull(session('coupon_code'));
        Mail::assertNothingQueued();
    }

    public function test_checkout_page_shows_the_applied_discount()
    {
        $user = User::factory()->create();
        $this->userCartWithSubtotal($user, 2000);
        $coupon = Coupon::factory()->percent(10)->create();

        $response = $this->actingAs($user)
            ->withSession(['coupon_code' => $coupon->code])
            ->get('/checkout');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('totals.discount_cents', 200)
            ->where('coupon', $coupon->code)
        );
    }

    private function cartWithSubtotal(int $cents): Cart
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['price_cents' => $cents]);
        CartItem::factory()->for($cart)->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        return $cart;
    }

    private function userCartWithSubtotal(User $user, int $cents): Cart
    {
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['price_cents' => $cents]);
        CartItem::factory()->for($cart)->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        return $cart;
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'ship_name' => 'Jane Doe',
            'ship_line1' => '123 Placebo Ave',
            'ship_city' => 'Madrid',
            'ship_postal_code' => '28001',
            'ship_country' => 'ES',
            'card_name' => 'Jane Doe',
            'card_number' => '4242 4242 4242 4242',
            'card_expiry' => '12/'.now()->addYears(2)->format('y'),
            'card_cvc' => '123',
        ];
    }
}
