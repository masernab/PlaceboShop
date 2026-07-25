<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    private const SORTS = ['newest', 'price_asc', 'price_desc', 'name'];

    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => (string) $request->query('category', ''),
            'min' => $request->filled('min') ? max(0, (int) $request->query('min')) : null,
            'max' => $request->filled('max') ? max(0, (int) $request->query('max')) : null,
            'sort' => in_array($request->query('sort'), self::SORTS, true)
                ? $request->query('sort')
                : 'newest',
        ];

        $products = Product::query()
            ->active()
            ->with(['primaryImage', 'category'])
            ->when($filters['q'] !== '', fn ($query) => $query->search($filters['q']))
            ->when($filters['category'] !== '', fn ($query) => $query->inCategory($filters['category']))
            ->priceBetween($filters['min'], $filters['max'])
            ->sorted($filters['sort'])
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->ordered()->withCount('products')->get();

        return Inertia::render('shop/products/index', [
            'products' => ProductCardResource::collection($products),
            'categories' => CategoryResource::collection($categories),
            'filters' => $filters,
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category']);

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['primaryImage', 'category'])
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('shop/products/show', [
            'product' => new ProductResource($product),
            'related' => ProductCardResource::collection($related),
        ]);
    }
}
