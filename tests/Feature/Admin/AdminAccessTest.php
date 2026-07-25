<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'admin'])->get('/admin-middleware-test', fn () => 'ok');
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get('/admin-middleware-test');

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_are_forbidden()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/admin-middleware-test');

        $response->assertForbidden();
    }

    public function test_admins_are_allowed()
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get('/admin-middleware-test');

        $response->assertOk();
    }
}
