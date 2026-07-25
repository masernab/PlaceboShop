<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function show(): Response
    {
        $cart = $this->cartService->current();
        $cart?->load(['items.product.primaryImage', 'items.product.category']);

        return Inertia::render('shop/cart', [
            'cart' => $cart === null ? null : new CartResource($cart),
            'totals' => $cart === null ? null : $this->cartService->totals($cart),
        ]);
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $product = Product::query()->findOrFail((int) $validated['product_id']);

        $this->cartService->add($product, (int) ($validated['quantity'] ?? 1));

        return back();
    }

    public function updateItem(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->ensureItemBelongsToCurrentCart($cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $this->cartService->updateQuantity($cartItem, (int) $validated['quantity']);

        return back();
    }

    public function destroyItem(CartItem $cartItem): RedirectResponse
    {
        $this->ensureItemBelongsToCurrentCart($cartItem);

        $this->cartService->remove($cartItem);

        return back();
    }

    /**
     * Guests have no policies, so ownership is checked against the cart
     * resolved from the current session or user.
     */
    private function ensureItemBelongsToCurrentCart(CartItem $cartItem): void
    {
        $cart = $this->cartService->current();

        abort_if($cart === null || $cartItem->cart_id !== $cart->id, 404);
    }
}
