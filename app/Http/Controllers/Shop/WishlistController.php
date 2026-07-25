<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->active()
            ->whereHas('wishlistItems', function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id);
            })
            ->with(['primaryImage', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->get();

        return Inertia::render('shop/wishlist', [
            'products' => ProductCardResource::collection($products),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlistItems()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return back();
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlistItems()
            ->where('product_id', $product->id)
            ->delete();

        return back();
    }
}
