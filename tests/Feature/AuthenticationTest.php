<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/y/1')->assertRedirect('/login');
        $this->get('/y/1/students')->assertRedirect('/login');
    }

    public function test_the_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('ورود به سیستم');
    }

    public function test_staff_can_log_in(): void
    {
        $user = User::factory()->create(['username' => 'admin', 'password' => 'secret-pass']);

        $this->post('/login', ['username' => 'admin', 'password' => 'secret-pass'])
            ->assertRedirect(route('years.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['username' => 'admin', 'password' => 'secret-pass']);

        $this->from('/login')
            ->post('/login', ['username' => 'admin', 'password' => 'nope'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_authenticated_staff_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_csrf_protection_is_active_on_writes(): void
    {
        // Laravel skips CSRF while the environment is "testing"; flip it for this one
        // assertion so the middleware is actually exercised.
        $this->app['env'] = 'local';

        $this->actingAs(User::factory()->create())
            ->post('/years', ['name' => '1403-1404'])
            ->assertStatus(419);
    }
}
