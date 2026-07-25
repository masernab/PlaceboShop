<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public const int SHIPPING_CENTS = 499;

    public const int FREE_SHIPPING_THRESHOLD_CENTS = 5000;

    public const int MAX_QUANTITY = 99;

    /**
     * Resolve the cart for the current user or guest session.
     */
    public function current(bool $create = false): ?Cart
    {
        $user = Auth::user();

        if ($user !== null) {
            return $create
                ? Cart::query()->firstOrCreate(['user_id' => $user->id])
                : Cart::query()->where('user_id', $user->id)->first();
        }

        $cartId = session('cart_id');
        $cart = is_int($cartId)
            ? Cart::query()->whereNull('user_id')->find($cartId)
            : null;

        if ($cart === null && $create) {
            $cart = Cart::query()->create();
            session(['cart_id' => $cart->id]);
        }

        return $cart;
    }

    /**
     * Add a product to the cart, consolidating existing lines.
     */
    public function add(Product $product, int $quantity = 1): CartItem
    {
        $cart = $this->current(create: true);
        assert($cart !== null);

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item !== null) {
            $item->update(['quantity' => $this->clamp($item->quantity + $quantity)]);

            return $item;
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $this->clamp($quantity),
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $item->update(['quantity' => $this->clamp($quantity)]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(): void
    {
        $this->current()?->items()->delete();
    }

    /**
     * Total number of units in the current cart, for the header badge.
     */
    public function itemCount(): int
    {
        return (int) ($this->current()?->items()->sum('quantity') ?? 0);
    }

    /**
     * The coupon applied in the current session, or null. A coupon that is
     * no longer redeemable for this cart is silently dropped from the session.
     */
    public function appliedCoupon(Cart $cart): ?Coupon
    {
        $code = session('coupon_code');

        if (! is_string($code)) {
            return null;
        }

        $coupon = Coupon::query()->where('code', $code)->first();

        if ($coupon === null || ! $coupon->isRedeemable($cart->subtotalCents())) {
            session()->forget('coupon_code');

            return null;
        }

        return $coupon;
    }

    /**
     * Compute the cart totals. Shipping is a flat fake fee, free above the
     * threshold (nothing ever ships either way).
     *
     * @return array{subtotal_cents: int, discount_cents: int, shipping_cents: int, total_cents: int}
     */
    public function totals(Cart $cart, ?Coupon $coupon = null): array
    {
        $subtotal = $cart->subtotalCents();

        $discount = $coupon !== null && $coupon->isRedeemable($subtotal)
            ? $coupon->discountFor($subtotal)
            : 0;

        $shipping = $subtotal === 0 || $subtotal >= self::FREE_SHIPPING_THRESHOLD_CENTS
            ? 0
            : self::SHIPPING_CENTS;

        return [
            'subtotal_cents' => $subtotal,
            'discount_cents' => $discount,
            'shipping_cents' => $shipping,
            'total_cents' => $subtotal - $discount + $shipping,
        ];
    }

    /**
     * Merge the guest session cart into the user's cart, summing quantities
     * on conflicting lines, then discard the guest cart.
     */
    public function mergeGuestCartIntoUser(User $user): void
    {
        $guestCartId = session('cart_id');

        if (! is_int($guestCartId)) {
            return;
        }

        session()->forget('cart_id');

        $guestCart = Cart::query()
            ->whereNull('user_id')
            ->with('items')
            ->find($guestCartId);

        if ($guestCart === null) {
            return;
        }

        $userCart = Cart::query()->firstOrCreate(['user_id' => $user->id]);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existing !== null) {
                $existing->update([
                    'quantity' => $this->clamp($existing->quantity + $guestItem->quantity),
                ]);
            } else {
                $userCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'quantity' => $guestItem->quantity,
                ]);
            }
        }

        $guestCart->delete();
    }

    private function clamp(int $quantity): int
    {
        return max(1, min(self::MAX_QUANTITY, $quantity));
    }
}
