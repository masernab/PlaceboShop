<?php

namespace Tests\Feature\Shop;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_a_product_to_the_cart()
    {
        $product = Product::factory()->create();

        $response = $this->from('/products')->post('/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect('/products');
        $this->assertNotNull(session('cart_id'));
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => session('cart_id'),
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_the_same_product_consolidates_the_line()
    {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create();
        CartItem::factory()->for($cart)->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->withSession(['cart_id' => $cart->id])->post('/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(5, $cart->items()->first()->quantity);
    }

    public function test_inactive_product_cannot_be_added()
    {
        $product = Product::factory()->inactive()->create();

        $response = $this->post('/cart/items', [
            'product_id' => $product->id,
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_can_update_item_quantity()
    {
        $cart = Cart::factory()->create();
        $item = CartItem::factory()->for($cart)->create(['quantity' => 1]);

        $this->withSession(['cart_id' => $cart->id])
            ->put("/cart/items/{$item->id}", ['quantity' => 4]);

        $this->assertSame(4, $item->fresh()->quantity);
    }

    public function test_quantity_is_clamped_to_the_maximum()
    {
        $cart = Cart::factory()->create();
        $item = CartItem::factory()->for($cart)->create(['quantity' => 1]);

        $this->withSession(['cart_id' => $cart->id])
            ->put("/cart/items/{$item->id}", ['quantity' => 500]);

        $this->assertSame(99, $item->fresh()->quantity);
    }

    public function test_guest_can_remove_an_item()
    {
        $cart = Cart::factory()->create();
        $item = CartItem::factory()->for($cart)->create();

        $this->withSession(['cart_id' => $cart->id])
            ->delete("/cart/items/{$item->id}");

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_item_of_another_cart_cannot_be_mutated()
    {
        $otherCart = Cart::factory()->create();
        $item = CartItem::factory()->for($otherCart)->create(['quantity' => 1]);

        $ownCart = Cart::factory()->create();

        $this->withSession(['cart_id' => $ownCart->id])
            ->put("/cart/items/{$item->id}", ['quantity' => 4])
            ->assertNotFound();

        $this->withSession(['cart_id' => $ownCart->id])
            ->delete("/cart/items/{$item->id}")
            ->assertNotFound();

        $this->assertSame(1, $item->fresh()->quantity);
    }

    public function test_authenticated_user_cannot_mutate_guest_cart_item()
    {
        $guestCart = Cart::factory()->create();
        $item = CartItem::factory()->for($guestCart)->create();

        $this->actingAs(User::factory()->create())
            ->delete("/cart/items/{$item->id}")
            ->assertNotFound();
    }

    public function test_login_merges_guest_cart_summing_quantities()
    {
        $sharedProduct = Product::factory()->create();
        $guestOnlyProduct = Product::factory()->create();

        $guestCart = Cart::factory()->create();
        CartItem::factory()->for($guestCart)->create([
            'product_id' => $sharedProduct->id,
            'quantity' => 2,
        ]);
        CartItem::factory()->for($guestCart)->create([
            'product_id' => $guestOnlyProduct->id,
            'quantity' => 1,
        ]);

        $user = User::factory()->create();
        $userCart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->for($userCart)->create([
            'product_id' => $sharedProduct->id,
            'quantity' => 1,
        ]);

        $this->withSession(['cart_id' => $guestCart->id])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);
        $this->assertNull(session('cart_id'));

        $this->assertSame(3, $userCart->items()
            ->where('product_id', $sharedProduct->id)->first()->quantity);
        $this->assertSame(1, $userCart->items()
            ->where('product_id', $guestOnlyProduct->id)->first()->quantity);
    }

    public function test_cart_badge_count_is_shared_with_all_pages()
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->for($cart)->create(['quantity' => 2]);
        CartItem::factory()->for($cart)->create(['quantity' => 3]);

        $response = $this->withSession(['cart_id' => $cart->id])->get('/');

        $response->assertInertia(
            fn (Assert $page) => $page->where('cart.count', 5)
        );
    }

    public function test_cart_page_shows_items_and_totals_with_shipping()
    {
        $product = Product::factory()->create(['price_cents' => 1000]);
        $cart = Cart::factory()->create();
        CartItem::factory()->for($cart)->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->withSession(['cart_id' => $cart->id])->get('/cart');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/cart')
            ->has('cart.data.items', 1)
            ->where('totals.subtotal_cents', 2000)
            ->where('totals.shipping_cents', 499)
            ->where('totals.total_cents', 2499)
        );
    }

    public function test_shipping_is_free_above_the_threshold()
    {
        $product = Product::factory()->create(['price_cents' => 3000]);
        $cart = Cart::factory()->create();
        CartItem::factory()->for($cart)->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->withSession(['cart_id' => $cart->id])->get('/cart');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('totals.subtotal_cents', 6000)
            ->where('totals.shipping_cents', 0)
            ->where('totals.total_cents', 6000)
        );
    }

    public function test_empty_cart_page_renders()
    {
        $response = $this->get('/cart');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/cart')
            ->where('cart', null)
            ->where('totals', null)
        );
    }
}
