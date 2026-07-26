<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_category_can_be_created()
    {
        $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => ['en' => 'Shoes', 'es' => 'Zapatos'],
            'description' => ['en' => 'Steps of joy.', 'es' => 'Pasos de alegría.'],
            'position' => 7,
        ]);

        $category = Category::query()->sole();
        $this->assertSame('shoes', $category->slug);
        $this->assertSame('Zapatos', $category->name['es']);
        $this->assertSame(7, $category->position);
        $this->assertNull($category->parent_id);
    }

    public function test_bilingual_name_is_required()
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => ['en' => 'Only English'],
            'position' => 0,
        ]);

        $response->assertSessionHasErrors('name.es');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_category_can_be_updated()
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => ['en' => 'Renamed', 'es' => 'Renombrada'],
            'description' => ['en' => '', 'es' => ''],
            'position' => 3,
        ]);

        $category->refresh();
        $this->assertSame('Renamed', $category->name['en']);
        $this->assertNull($category->description);
        $this->assertSame(3, $category->position);
    }

    public function test_empty_category_can_be_deleted()
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_products_cannot_be_deleted()
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/categories/{$category->id}");

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_subcategory_can_be_created_under_a_parent()
    {
        $parent = Category::factory()->create();

        $this->actingAs($this->admin)->post('/admin/categories', [
            'parent_id' => $parent->id,
            'name' => ['en' => 'Rings', 'es' => 'Anillos'],
            'position' => 1,
        ]);

        $child = Category::query()->where('slug', 'rings')->sole();
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertTrue($parent->children()->whereKey($child->id)->exists());
    }

    public function test_parent_must_be_a_top_level_category()
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'parent_id' => $child->id,
            'name' => ['en' => 'Too deep', 'es' => 'Muy profundo'],
            'position' => 0,
        ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('categories', ['slug' => 'too-deep']);
    }

    public function test_category_cannot_become_its_own_parent()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'parent_id' => $category->id,
            'name' => ['en' => 'Loop', 'es' => 'Bucle'],
            'position' => 0,
        ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertNull($category->refresh()->parent_id);
    }

    public function test_category_with_subcategories_cannot_become_a_subcategory()
    {
        $parent = Category::factory()->create();
        Category::factory()->childOf($parent)->create();
        $other = Category::factory()->create();

        $response = $this->actingAs($this->admin)->put("/admin/categories/{$parent->id}", [
            'parent_id' => $other->id,
            'name' => ['en' => 'Moved', 'es' => 'Movida'],
            'position' => 0,
        ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertNull($parent->refresh()->parent_id);
    }

    public function test_subcategory_can_be_promoted_to_top_level()
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        $this->actingAs($this->admin)->put("/admin/categories/{$child->id}", [
            'parent_id' => null,
            'name' => ['en' => 'Promoted', 'es' => 'Promovida'],
            'position' => 2,
        ]);

        $this->assertNull($child->refresh()->parent_id);
    }

    public function test_category_with_subcategories_cannot_be_deleted()
    {
        $parent = Category::factory()->create();
        Category::factory()->childOf($parent)->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/categories/{$parent->id}");

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    public function test_index_returns_categories_as_a_tree()
    {
        $parent = Category::factory()->create();
        Category::factory()->childOf($parent)->create();

        $this->actingAs($this->admin)->get('/admin/categories')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/categories/index')
                ->has('categories.data', 1)
                ->has('categories.data.0.children', 1)
                ->where('categories.data.0.parent_id', null)
            );
    }
}
