<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $latestOrders = Order::query()
            ->with('user')
            ->latest('placed_at')
            ->take(5)
            ->get();

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'orders_today' => Order::query()->whereDate('placed_at', today())->count(),
                'customers' => User::query()->where('is_admin', false)->count(),
                'pretend_revenue_cents' => (int) Order::query()
                    ->whereNull('cancelled_at')
                    ->sum('total_cents'),
            ],
            'latestOrders' => OrderResource::collection($latestOrders),
        ]);
    }
}
