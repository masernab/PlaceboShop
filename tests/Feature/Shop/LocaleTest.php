<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_can_be_changed()
    {
        $response = $this->from('/')->put(route('locale.update'), ['locale' => 'es']);

        $response->assertRedirect('/');
        $this->assertSame('es', session('locale'));
    }

    public function test_invalid_locale_is_rejected()
    {
        $response = $this->from('/')->put(route('locale.update'), ['locale' => 'fr']);

        $response->assertSessionHasErrors('locale');
        $this->assertNull(session('locale'));
    }

    public function test_locale_is_shared_with_inertia_pages()
    {
        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('locale', 'es'));
    }

    public function test_default_locale_is_english()
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
    }
}
