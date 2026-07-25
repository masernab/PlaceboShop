<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with(['category', 'primaryImage'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/products/index', [
            'products' => ProductResource::collection($products),
            'filters' => ['q' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/create', [
            'categories' => CategoryResource::collection(
                Category::query()->ordered()->get(),
            ),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $attributes = $request->productAttributes();

        $product = Product::query()->create([
            ...$attributes,
            'slug' => $this->uniqueSlug($attributes['name']['en']),
            'sku' => $this->uniqueSku(),
        ]);

        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(Product $product): Response
    {
        $product->load(['images']);

        return Inertia::render('admin/products/edit', [
            'product' => new ProductResource($product),
            'categories' => CategoryResource::collection(
                Category::query()->ordered()->get(),
            ),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->productAttributes());

        return back();
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base === '' ? 'product' : $base;
        $suffix = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function uniqueSku(): string
    {
        do {
            $sku = 'PB-'.strtoupper(Str::random(6));
        } while (Product::query()->where('sku', $sku)->exists());

        return $sku;
    }
}
