<?php

namespace Tests\Feature\Shop;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_requires_authentication()
    {
        $product = Product::factory()->create();

        $this->get('/wishlist')->assertRedirect('/login');
        $this->post("/wishlist/{$product->id}")->assertRedirect('/login');
        $this->delete("/wishlist/{$product->id}")->assertRedirect('/login');
    }

    public function test_product_can_be_added_and_removed()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post("/wishlist/{$product->id}");

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->delete("/wishlist/{$product->id}");

        $this->assertDatabaseCount('wishlist_items', 0);
    }

    public function test_adding_twice_keeps_a_single_row()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post("/wishlist/{$product->id}");
        $this->actingAs($user)->post("/wishlist/{$product->id}");

        $this->assertDatabaseCount('wishlist_items', 1);
    }

    public function test_wishlist_page_lists_saved_products()
    {
        $user = User::factory()->create();
        $saved = Product::factory()->create();
        Product::factory()->create();

        WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $saved->id,
        ]);

        $response = $this->actingAs($user)->get('/wishlist');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/wishlist')
            ->has('products.data', 1)
            ->where('products.data.0.id', $saved->id)
        );
    }

    public function test_wishlist_ids_are_shared_with_all_pages()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertInertia(
            fn (Assert $page) => $page->where('wishlist', [$product->id])
        );
    }
}
