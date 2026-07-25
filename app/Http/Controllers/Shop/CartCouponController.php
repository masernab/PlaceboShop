<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartCouponController extends Controller
{
    public function __construct(private CartService $cartService) {}

    /**
     * Apply a coupon code to the current session. Errors are returned as
     * stable keys the frontend translates to the active locale.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $cart = $this->cartService->current();

        if ($cart === null || $cart->items()->count() === 0) {
            return redirect()->route('cart.show');
        }

        $code = strtoupper(trim($validated['code']));
        $coupon = Coupon::query()->where('code', $code)->first();

        if ($coupon === null) {
            throw ValidationException::withMessages(['code' => 'coupon.error_not_found']);
        }

        if (! $coupon->is_active || ! $coupon->hasStarted() || $coupon->hasExpired()) {
            throw ValidationException::withMessages(['code' => 'coupon.error_expired']);
        }

        if ($coupon->isExhausted()) {
            throw ValidationException::withMessages(['code' => 'coupon.error_exhausted']);
        }

        if (! $coupon->meetsMinimum($cart->subtotalCents())) {
            throw ValidationException::withMessages(['code' => 'coupon.error_min']);
        }

        session(['coupon_code' => $coupon->code]);

        return back();
    }

    public function destroy(): RedirectResponse
    {
        session()->forget('coupon_code');

        return back();
    }
}
