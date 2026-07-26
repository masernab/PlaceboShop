<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;
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
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($filters['q'] !== '', fn ($query) => $query->search($filters['q']))
            ->when($filters['category'] !== '', fn ($query) => $query->inCategory($filters['category']))
            ->priceBetween($filters['min'], $filters['max'])
            ->sorted($filters['sort'])
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->roots()
            ->ordered()
            ->withCount(['products', 'childProducts'])
            ->with(['children' => fn (Relation $query) => $query->withCount('products')])
            ->get();

        return Inertia::render('shop/products/index', [
            'products' => ProductCardResource::collection($products),
            'categories' => CategoryResource::collection($categories),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category.parent']);
        $product->loadAvg('reviews', 'rating');
        $product->loadCount('reviews');

        $reviews = $product->reviews()->with('user')->latest()->get();

        $user = $request->user();
        $canReview = $user !== null
            && $user->hasPurchased($product)
            && ! $reviews->contains(fn ($review): bool => $review->user_id === $user->id);

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['primaryImage', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('shop/products/show', [
            'product' => new ProductResource($product),
            'reviews' => ReviewResource::collection($reviews),
            'canReview' => $canReview,
            'related' => ProductCardResource::collection($related),
        ]);
    }
}
