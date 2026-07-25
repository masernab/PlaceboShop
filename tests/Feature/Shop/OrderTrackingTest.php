<?php

namespace Tests\Feature\Shop;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_advances_with_time()
    {
        $placedAt = Carbon::parse('2026-07-01 10:00:00');
        $order = Order::factory()->placedAt($placedAt)->create();

        $expectations = [
            [5, OrderStatus::Paid],
            [10, OrderStatus::Processing],
            [6 * 60, OrderStatus::Shipped],
            [30 * 60, OrderStatus::OutForDelivery],
            [54 * 60, OrderStatus::Delivered],
            [90 * 24 * 60, OrderStatus::Delivered],
        ];

        foreach ($expectations as [$minutes, $expected]) {
            $this->travelTo($placedAt->copy()->addMinutes($minutes));

            $this->assertSame(
                $expected,
                $order->fresh()->status,
                "Expected {$expected->value} at +{$minutes} minutes",
            );
        }
    }

    public function test_cancelled_at_overrides_the_computed_status()
    {
        $placedAt = Carbon::parse('2026-07-01 10:00:00');
        $order = Order::factory()->placedAt($placedAt)->cancelled()->create();

        $this->travelTo($placedAt->copy()->addDays(10));

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);

        $timeline = $order->fresh()->timeline();
        $this->assertTrue($timeline[0]['reached']);
        $this->assertFalse($timeline[2]['reached']);
    }

    public function test_timeline_has_five_stages_with_timestamps()
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create();

        $response = $this->actingAs($user)->get("/orders/{$order->id}");

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/orders/show')
            ->has('order.data.timeline', 5)
            ->where('order.data.timeline.0.status', 'paid')
            ->where('order.data.timeline.0.reached', true)
            ->where('order.data.timeline.4.status', 'delivered')
            ->where('justPlaced', false)
        );
    }

    public function test_orders_index_lists_own_orders_latest_first()
    {
        $user = User::factory()->create();
        $old = Order::factory()->for($user)->placedAt(now()->subDays(5))->create();
        $recent = Order::factory()->for($user)->placedAt(now()->subHours(1))->create();
        Order::factory()->create();

        $response = $this->actingAs($user)->get('/orders');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('shop/orders/index')
            ->has('orders.data', 2)
            ->where('orders.data.0.order_number', $recent->order_number)
            ->where('orders.data.1.order_number', $old->order_number)
        );
    }

    public function test_foreign_order_returns_403()
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get("/orders/{$order->id}")
            ->assertForbidden();
    }
}
