<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::query()->where('email', 'demo@placeboshop.test')->first();

        if ($demo === null || $demo->orders()->exists()) {
            return;
        }

        // One delivered order from three days ago and one still processing,
        // so the demo account shows the fake tracking in different stages.
        $this->createOrder($demo, now()->subDays(3), Product::query()->with('primaryImage')->take(2)->get());
        $this->createOrder($demo, now()->subMinutes(20), Product::query()->with('primaryImage')->skip(2)->take(1)->get());
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function createOrder(User $user, CarbonInterface $placedAt, Collection $products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $subtotal = (int) $products->sum('price_cents');
        $shipping = $subtotal >= 5000 ? 0 : 499;

        $order = Order::factory()
            ->placedAt($placedAt)
            ->create([
                'user_id' => $user->id,
                'ship_name' => $user->name,
                'subtotal_cents' => $subtotal,
                'shipping_cents' => $shipping,
                'total_cents' => $subtotal + $shipping,
            ]);

        foreach ($products as $product) {
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price_cents' => $product->price_cents,
                'quantity' => 1,
                'image_path' => $product->primaryImage?->path,
            ]);
        }
    }
}
