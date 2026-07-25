<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featured = Product::query()
            ->active()
            ->featured()
            ->with(['primaryImage', 'category'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::query()->ordered()->get();

        return Inertia::render('shop/home', [
            'featured' => ProductCardResource::collection($featured),
            'categories' => CategoryResource::collection($categories),
        ]);
    }
}
