<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_featured_products_and_categories()
    {
        $category = Category::factory()->create();
        Product::factory()->featured()->for($category)->create();
        Product::factory()->for($category)->create();

        $response = $this->get('/');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/home')
            ->has('featured.data', 1)
            ->has('categories.data', 1)
        );
    }

    public function test_index_lists_active_products_only()
    {
        Product::factory()->create();
        Product::factory()->inactive()->create();

        $response = $this->get('/products');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/products/index')
            ->has('products.data', 1)
            ->where('products.meta.total', 1)
        );
    }

    public function test_index_filters_by_category()
    {
        $fashion = Category::factory()->create(['slug' => 'fashion']);
        $beauty = Category::factory()->create(['slug' => 'beauty']);
        Product::factory()->for($fashion)->count(2)->create();
        Product::factory()->for($beauty)->create();

        $response = $this->get('/products?category=fashion');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 2)
            ->where('filters.category', 'fashion')
        );
    }

    public function test_index_filters_by_price_range()
    {
        Product::factory()->create(['price_cents' => 1000]);
        Product::factory()->create(['price_cents' => 5000]);
        Product::factory()->create(['price_cents' => 9000]);

        $response = $this->get('/products?min=2000&max=8000');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.price_cents', 5000)
        );
    }

    public function test_index_searches_both_languages()
    {
        Product::factory()->create([
            'name' => ['en' => 'Moonstone Pendant', 'es' => 'Colgante Piedra Lunar'],
        ]);
        Product::factory()->create([
            'name' => ['en' => 'Silk Scarf', 'es' => 'Pañuelo de Seda'],
        ]);

        $this->get('/products?q=Moonstone')->assertInertia(
            fn (Assert $page) => $page->has('products.data', 1)
        );

        $this->get('/products?q=Pañuelo')->assertInertia(
            fn (Assert $page) => $page->has('products.data', 1)
        );
    }

    public function test_index_sorts_by_price()
    {
        Product::factory()->create(['price_cents' => 5000]);
        Product::factory()->create(['price_cents' => 1000]);
        Product::factory()->create(['price_cents' => 9000]);

        $response = $this->get('/products?sort=price_asc');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.price_cents', 1000)
            ->where('products.data.2.price_cents', 9000)
        );
    }

    public function test_invalid_sort_falls_back_to_newest()
    {
        Product::factory()->create();

        $response = $this->get('/products?sort=evil');

        $response->assertOk()->assertInertia(
            fn (Assert $page) => $page->where('filters.sort', 'newest')
        );
    }

    public function test_show_displays_a_product()
    {
        $product = Product::factory()->create([
            'name' => ['en' => 'Moonstone Pendant', 'es' => 'Colgante Piedra Lunar'],
        ]);

        $response = $this->get("/products/{$product->slug}");

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/products/show')
            ->where('product.data.name', 'Moonstone Pendant')
            ->where('product.data.slug', $product->slug)
        );
    }

    public function test_show_localizes_content_from_session()
    {
        $product = Product::factory()->create([
            'name' => ['en' => 'Moonstone Pendant', 'es' => 'Colgante Piedra Lunar'],
        ]);

        $response = $this->withSession(['locale' => 'es'])
            ->get("/products/{$product->slug}");

        $response->assertInertia(fn (Assert $page) => $page
            ->where('product.data.name', 'Colgante Piedra Lunar')
        );
    }

    public function test_inactive_product_returns_404()
    {
        $product = Product::factory()->inactive()->create();

        $this->get("/products/{$product->slug}")->assertNotFound();
    }

    public function test_show_includes_related_products_from_same_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        Product::factory()->for($category)->count(2)->create();
        Product::factory()->create();

        $response = $this->get("/products/{$product->slug}");

        $response->assertInertia(
            fn (Assert $page) => $page->has('related.data', 2)
        );
    }
}
