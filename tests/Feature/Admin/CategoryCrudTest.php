<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
