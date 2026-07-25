<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasPurchased($product), 403);
        abort_if($product->reviews()->where('user_id', $user->id)->exists(), 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
        ]);

        return back();
    }
}
