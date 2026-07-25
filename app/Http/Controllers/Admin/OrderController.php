<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::query()
            ->with('user')
            ->latest('placed_at')
            ->paginate(20);

        return Inertia::render('admin/orders/index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items', 'user']);

        return Inertia::render('admin/orders/show', [
            'order' => new OrderResource($order),
        ]);
    }
}
