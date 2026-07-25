<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreOrderRequest;
use App\Http\Resources\CartResource;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Services\CartService;
use App\Support\CardBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    private const string DECLINED_CARD = '4000000000000002';

    public function __construct(private CartService $cartService) {}

    public function show(): Response|RedirectResponse
    {
        $cart = $this->cartService->current();
        $cart?->load(['items.product.primaryImage', 'items.product.category']);

        if ($cart === null || $cart->items->isEmpty()) {
            return redirect()->route('cart.show');
        }

        return Inertia::render('shop/checkout', [
            'cart' => new CartResource($cart),
            'totals' => $this->cartService->totals($cart),
            'countries' => StoreOrderRequest::COUNTRIES,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $cart = $this->cartService->current();
        $cart?->load('items.product.primaryImage');

        if ($cart === null || $cart->items->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $validated = $request->validated();
        $cardDigits = str_replace(' ', '', (string) $validated['card_number']);

        if ($cardDigits === self::DECLINED_CARD) {
            throw ValidationException::withMessages([
                'card_number' => 'Your card was declined. (Simulated — try 4242 4242 4242 4242.)',
            ]);
        }

        $totals = $this->cartService->totals($cart);
        $user = $request->user();

        $order = DB::transaction(function () use ($cart, $validated, $cardDigits, $totals, $user): Order {
            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'placed_at' => now(),
                'subtotal_cents' => $totals['subtotal_cents'],
                'discount_cents' => $totals['discount_cents'],
                'shipping_cents' => $totals['shipping_cents'],
                'total_cents' => $totals['total_cents'],
                'card_brand' => CardBrand::detect($cardDigits),
                'card_last4' => substr($cardDigits, -4),
                'tracking_number' => $this->generateTrackingNumber(),
                'ship_name' => $validated['ship_name'],
                'ship_line1' => $validated['ship_line1'],
                'ship_line2' => $validated['ship_line2'] ?? null,
                'ship_city' => $validated['ship_city'],
                'ship_postal_code' => $validated['ship_postal_code'],
                'ship_country' => $validated['ship_country'],
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'unit_price_cents' => $item->product->price_cents,
                    'quantity' => $item->quantity,
                    'image_path' => $item->product->primaryImage?->path,
                ]);
            }

            $cart->items()->delete();

            return $order;
        });

        Mail::to($user)->queue(new OrderConfirmationMail($order, app()->getLocale()));

        return redirect()
            ->route('orders.show', $order)
            ->with('justPlaced', true);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = sprintf('PB-%d-%s', now()->year, strtoupper(Str::random(6)));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    private function generateTrackingNumber(): string
    {
        return 'PBX'.str_pad((string) random_int(0, 9_999_999_999), 10, '0', STR_PAD_LEFT);
    }
}
