<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_product_can_be_created_with_bilingual_fields()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => ['en' => 'Dream Slippers', 'es' => 'Pantuflas de Ensueño'],
            'description' => ['en' => 'So comfy.', 'es' => 'Comodísimas.'],
            'price' => '49.90',
            'compare_at_price' => '59.90',
            'stock' => 12,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $product = Product::query()->sole();

        $response->assertRedirect(route('admin.products.edit', $product));
        $this->assertSame('dream-slippers', $product->slug);
        $this->assertSame(4990, $product->price_cents);
        $this->assertSame(5990, $product->compare_at_price_cents);
        $this->assertSame('Pantuflas de Ensueño', $product->name['es']);
        $this->assertMatchesRegularExpression('/^PB-[A-Z0-9]{6}$/', $product->sku);
    }

    public function test_both_languages_are_required()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => ['en' => 'Only English'],
            'description' => ['en' => 'Only English.', 'es' => 'Español.'],
            'price' => '10.00',
            'stock' => 1,
        ]);

        $response->assertSessionHasErrors('name.es');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_can_be_updated()
    {
        $product = Product::factory()->create(['price_cents' => 1000]);

        $this->actingAs($this->admin)->put("/admin/products/{$product->id}", [
            'category_id' => $product->category_id,
            'name' => ['en' => 'Renamed', 'es' => 'Renombrado'],
            'description' => ['en' => 'Updated.', 'es' => 'Actualizado.'],
            'price' => '25.00',
            'compare_at_price' => null,
            'stock' => 3,
            'is_active' => false,
            'is_featured' => true,
        ]);

        $product->refresh();
        $this->assertSame(2500, $product->price_cents);
        $this->assertNull($product->compare_at_price_cents);
        $this->assertSame('Renamed', $product->name['en']);
        $this->assertFalse($product->is_active);
        $this->assertTrue($product->is_featured);
    }

    public function test_product_can_be_deleted()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/products/{$product->id}");

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_images_can_be_uploaded_and_deleted()
    {
        Storage::fake('public_uploads');

        $product = Product::factory()->create();

        $this->actingAs($this->admin)->post(
            "/admin/products/{$product->id}/images",
            [
                'images' => [
                    UploadedFile::fake()->image('front.jpg', 800, 1000),
                    UploadedFile::fake()->image('back.jpg', 800, 1000),
                ],
            ],
        );

        $this->assertSame(2, $product->images()->count());

        $image = $product->images()->orderBy('position')->first();
        $this->assertStringStartsWith('uploads/products/', $image->path);
        $this->assertStringEndsWith('.webp', $image->path);
        Storage::disk('public_uploads')->assertExists(
            substr($image->path, strlen('uploads/')),
        );

        $this->actingAs($this->admin)->delete("/admin/product-images/{$image->id}");

        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('public_uploads')->assertMissing(
            substr($image->path, strlen('uploads/')),
        );
    }

    public function test_oversized_uploads_are_scaled_down_and_recompressed()
    {
        Storage::fake('public_uploads');

        $product = Product::factory()->create();
        $upload = UploadedFile::fake()->image('huge.jpg', 4000, 3000);
        $originalSize = $upload->getSize();

        $this->actingAs($this->admin)->post(
            "/admin/products/{$product->id}/images",
            ['images' => [$upload]],
        );

        $image = $product->images()->sole();
        $disk = Storage::disk('public_uploads');
        $relative = substr($image->path, strlen('uploads/'));

        [$width, $height] = getimagesizefromstring($disk->get($relative));

        $this->assertLessThanOrEqual(config('images.products.max_width'), $width);
        $this->assertLessThanOrEqual(config('images.products.max_height'), $height);
        $this->assertSame(4 / 3, $width / $height);
        $this->assertLessThan($originalSize, $disk->size($relative));
    }

    public function test_non_image_uploads_are_rejected()
    {
        Storage::fake('public_uploads');

        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->post(
            "/admin/products/{$product->id}/images",
            ['images' => [UploadedFile::fake()->create('malware.pdf', 100)]],
        );

        $response->assertSessionHasErrors('images.0');
        $this->assertSame(0, $product->images()->count());
    }

    public function test_images_can_be_reordered()
    {
        $product = Product::factory()->create();
        $first = ProductImage::factory()->for($product)->create(['position' => 0]);
        $second = ProductImage::factory()->for($product)->create(['position' => 1]);

        $this->actingAs($this->admin)->put(
            "/admin/product-images/{$second->id}",
            ['direction' => 'up'],
        );

        $this->assertSame(1, $first->fresh()->position);
        $this->assertSame(0, $second->fresh()->position);
    }
}
