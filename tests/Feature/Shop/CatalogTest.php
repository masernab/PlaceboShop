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
        Category::factory()->childOf($category)->create();
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

    public function test_index_filters_by_parent_category_including_subcategories()
    {
        $fashion = Category::factory()->create(['slug' => 'fashion']);
        $dresses = Category::factory()->childOf($fashion)->create(['slug' => 'dresses']);
        $beauty = Category::factory()->create(['slug' => 'beauty']);
        Product::factory()->for($fashion)->create();
        Product::factory()->for($dresses)->count(2)->create();
        Product::factory()->for($beauty)->create();

        $response = $this->get('/products?category=fashion');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 3)
            ->where('filters.category', 'fashion')
        );
    }

    public function test_index_filters_by_subcategory_excludes_the_rest_of_the_branch()
    {
        $fashion = Category::factory()->create(['slug' => 'fashion']);
        $dresses = Category::factory()->childOf($fashion)->create(['slug' => 'dresses']);
        Category::factory()->childOf($fashion)->create(['slug' => 'tops']);
        Product::factory()->for($fashion)->create();
        Product::factory()->for($dresses)->create();

        $response = $this->get('/products?category=dresses');

        $response->assertOk()->assertInertia(
            fn (Assert $page) => $page->has('products.data', 1)
        );
    }

    public function test_index_nests_subcategories_under_their_parent()
    {
        $fashion = Category::factory()->create(['slug' => 'fashion']);
        Category::factory()->childOf($fashion)->create(['slug' => 'dresses']);

        $response = $this->get('/products');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('categories.data', 1)
            ->has('categories.data.0.children', 1)
            ->where('categories.data.0.children.0.slug', 'dresses')
        );
    }

    public function test_parent_category_count_includes_subcategory_products()
    {
        $fashion = Category::factory()->create(['slug' => 'fashion']);
        $dresses = Category::factory()->childOf($fashion)->create();
        Product::factory()->for($fashion)->create();
        Product::factory()->for($dresses)->count(2)->create();

        $this->get('/products')->assertInertia(fn (Assert $page) => $page
            ->where('categories.data.0.products_count', 3)
            ->where('categories.data.0.children.0.products_count', 2)
        );
    }

    public function test_category_counts_ignore_inactive_products()
    {
        $fashion = Category::factory()->create(['slug' => 'fashion']);
        $dresses = Category::factory()->childOf($fashion)->create();
        Product::factory()->for($fashion)->create();
        Product::factory()->for($fashion)->inactive()->create();
        Product::factory()->for($dresses)->create();
        Product::factory()->for($dresses)->inactive()->count(2)->create();

        $this->get('/products')->assertInertia(fn (Assert $page) => $page
            ->where('categories.data.0.products_count', 2)
            ->where('categories.data.0.children.0.products_count', 1)
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

    public function test_show_exposes_the_parent_category_for_the_breadcrumb()
    {
        $fashion = Category::factory()->create(['slug' => 'fashion']);
        $dresses = Category::factory()->childOf($fashion)->create(['slug' => 'dresses']);
        $product = Product::factory()->for($dresses)->create();

        $response = $this->get("/products/{$product->slug}");

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('product.data.category.slug', 'dresses')
            ->where('product.data.category.parent.slug', 'fashion')
        );
    }

    public function test_show_has_no_parent_for_a_top_level_category()
    {
        $product = Product::factory()->create();

        $this->get("/products/{$product->slug}")->assertInertia(
            fn (Assert $page) => $page->where('product.data.category.parent', null)
        );
    }
}
