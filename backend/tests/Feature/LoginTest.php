<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_credentials_log_in_and_redirect_to_the_angular_app(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(config('app.frontend_url'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_returns_a_catalan_error_and_keeps_the_email(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => __('auth.failed')])
            ->assertSessionHasInput('email', $user->email);

        $this->assertGuest();
    }
}
