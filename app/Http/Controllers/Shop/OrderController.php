<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = $request->user()->orders()
            ->with('items')
            ->latest('placed_at')
            ->get();

        return Inertia::render('shop/orders/index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        Gate::authorize('view', $order);

        $order->load('items');

        return Inertia::render('shop/orders/show', [
            'order' => new OrderResource($order),
            'justPlaced' => (bool) $request->session()->get('justPlaced', false),
        ]);
    }
}
