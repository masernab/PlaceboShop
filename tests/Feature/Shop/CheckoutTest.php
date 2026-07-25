<?php

namespace Tests\Feature\Shop;

use App\Mail\OrderConfirmationMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login()
    {
        $this->get('/checkout')->assertRedirect('/login');
    }

    public function test_empty_cart_redirects_to_cart_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/checkout')->assertRedirect('/cart');
        $this->actingAs($user)
            ->post('/checkout', $this->validPayload())
            ->assertRedirect('/cart');
    }

    public function test_checkout_page_renders_with_cart_summary()
    {
        $user = User::factory()->create();
        $this->fillCart($user, price: 1000, quantity: 2);

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/checkout')
            ->has('cart.data.items', 1)
            ->where('totals.subtotal_cents', 2000)
            ->has('countries')
        );
    }

    public function test_successful_checkout_creates_order_with_snapshots()
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = $this->fillCart($user, price: 1000, quantity: 2);

        $response = $this->actingAs($user)->post('/checkout', $this->validPayload());

        $order = Order::query()->sole();

        $response->assertRedirect(route('orders.show', $order));
        $response->assertSessionHas('justPlaced', true);

        $this->assertMatchesRegularExpression('/^PB-\d{4}-[A-Z0-9]{6}$/', $order->order_number);
        $this->assertMatchesRegularExpression('/^PBX\d{10}$/', $order->tracking_number);
        $this->assertSame(2000, $order->subtotal_cents);
        $this->assertSame(499, $order->shipping_cents);
        $this->assertSame(2499, $order->total_cents);
        $this->assertSame('visa', $order->card_brand);
        $this->assertSame('4242', $order->card_last4);

        $item = $order->items()->sole();
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame($product->name, $item->product_name);
        $this->assertSame(1000, $item->unit_price_cents);
        $this->assertSame(2, $item->quantity);

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(0, CartItem::query()->count());

        Mail::assertQueued(OrderConfirmationMail::class);
    }

    public function test_pan_and_cvc_are_never_persisted()
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->fillCart($user);

        $this->actingAs($user)->post('/checkout', $this->validPayload());

        $columns = Schema::getColumnListing('orders');
        $this->assertNotContains('card_number', $columns);
        $this->assertNotContains('card_cvc', $columns);

        $row = json_encode((array) DB::table('orders')->first());
        $this->assertStringNotContainsString('4242424242424242', $row);
        $this->assertStringNotContainsString('4242 4242 4242 4242', $row);
    }

    public function test_declined_card_produces_error_and_no_order()
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->fillCart($user);

        $response = $this->actingAs($user)->post('/checkout', $this->validPayload([
            'card_number' => '4000 0000 0000 0002',
        ]));

        $response->assertSessionHasErrors('card_number');
        $this->assertDatabaseCount('orders', 0);
        $this->assertNotSame(0, CartItem::query()->count());
        Mail::assertNothingQueued();
    }

    public function test_invalid_luhn_card_is_rejected()
    {
        $user = User::factory()->create();
        $this->fillCart($user);

        $response = $this->actingAs($user)->post('/checkout', $this->validPayload([
            'card_number' => '4242 4242 4242 4241',
        ]));

        $response->assertSessionHasErrors('card_number');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_expired_card_is_rejected()
    {
        $user = User::factory()->create();
        $this->fillCart($user);

        $response = $this->actingAs($user)->post('/checkout', $this->validPayload([
            'card_expiry' => '01/20',
        ]));

        $response->assertSessionHasErrors('card_expiry');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_confirmation_page_receives_just_placed_flag()
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->fillCart($user);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post('/checkout', $this->validPayload());

        $response->assertInertia(fn (Assert $page) => $page
            ->component('shop/orders/show')
            ->where('justPlaced', true)
        );
    }

    private function fillCart(User $user, int $price = 1000, int $quantity = 1): Product
    {
        $product = Product::factory()->create(['price_cents' => $price]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->for($cart)->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);

        return $product;
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'ship_name' => 'Jane Doe',
            'ship_line1' => '123 Placebo Ave',
            'ship_city' => 'Madrid',
            'ship_postal_code' => '28001',
            'ship_country' => 'ES',
            'card_name' => 'Jane Doe',
            'card_number' => '4242 4242 4242 4242',
            'card_expiry' => '12/'.now()->addYears(2)->format('y'),
            'card_cvc' => '123',
        ], $overrides);
    }
}
