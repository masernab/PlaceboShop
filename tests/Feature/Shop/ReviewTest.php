<?php

namespace Tests\Feature\Shop;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_purchase_cannot_review()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post(
            "/products/{$product->slug}/reviews",
            ['rating' => 5, 'body' => 'Amazing.'],
        );

        $response->assertForbidden();
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_buyer_can_review_a_purchased_product()
    {
        [$user, $product] = $this->buyerWithPurchase();

        $response = $this->actingAs($user)->post(
            "/products/{$product->slug}/reviews",
            ['rating' => 4, 'title' => 'Lovely', 'body' => 'Feels great.'],
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'title' => 'Lovely',
        ]);
    }

    public function test_only_one_review_per_product()
    {
        [$user, $product] = $this->buyerWithPurchase();

        Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(
            "/products/{$product->slug}/reviews",
            ['rating' => 5, 'body' => 'Again!'],
        );

        $response->assertForbidden();
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_rating_must_be_between_one_and_five()
    {
        [$user, $product] = $this->buyerWithPurchase();

        foreach ([0, 6] as $rating) {
            $this->actingAs($user)
                ->post("/products/{$product->slug}/reviews", [
                    'rating' => $rating,
                    'body' => 'Out of bounds.',
                ])
                ->assertSessionHasErrors('rating');
        }

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_reviews_and_average_appear_on_the_product_page()
    {
        $product = Product::factory()->create();
        Review::factory()->create(['product_id' => $product->id, 'rating' => 5]);
        Review::factory()->create(['product_id' => $product->id, 'rating' => 4]);

        $response = $this->get("/products/{$product->slug}");

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('reviews.data', 2)
            ->where('product.data.rating_avg', 4.5)
            ->where('product.data.reviews_count', 2)
            ->where('canReview', false)
        );
    }

    public function test_can_review_prop_reflects_purchase_and_existing_review()
    {
        [$user, $product] = $this->buyerWithPurchase();

        $this->actingAs($user)
            ->get("/products/{$product->slug}")
            ->assertInertia(fn (Assert $page) => $page->where('canReview', true));

        Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get("/products/{$product->slug}")
            ->assertInertia(fn (Assert $page) => $page->where('canReview', false));
    }

    /**
     * @return array{0: User, 1: Product}
     */
    private function buyerWithPurchase(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->for($user)->create();
        OrderItem::factory()->for($order)->create(['product_id' => $product->id]);

        return [$user, $product];
    }
}
