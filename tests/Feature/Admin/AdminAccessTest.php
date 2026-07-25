<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every GET route of the admin panel.
     *
     * @return list<string>
     */
    private function adminUrls(): array
    {
        $product = Product::factory()->create();
        Category::factory()->create();
        Coupon::factory()->create();
        $order = Order::factory()->create();

        return [
            '/admin',
            '/admin/products',
            '/admin/products/create',
            "/admin/products/{$product->id}/edit",
            '/admin/categories',
            '/admin/coupons',
            '/admin/orders',
            "/admin/orders/{$order->id}",
        ];
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        foreach ($this->adminUrls() as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_regular_users_are_forbidden()
    {
        $user = User::factory()->create();

        foreach ($this->adminUrls() as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_admins_are_allowed()
    {
        $admin = User::factory()->admin()->create();

        foreach ($this->adminUrls() as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
