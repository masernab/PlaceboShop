<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_to_orders()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect('/orders');
    }

    public function test_guests_end_up_on_the_login_page()
    {
        $response = $this->followingRedirects()->get(route('dashboard'));
        $response->assertOk();

        $this->get('/orders')->assertRedirect(route('login'));
    }
}
