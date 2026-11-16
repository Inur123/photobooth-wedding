<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_fails_with_invalid_turnstile_token(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret-key']);

        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
    }

    public function test_login_succeeds_with_valid_turnstile_token(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret-key']);

        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'valid-token',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_register_fails_with_invalid_turnstile_token(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret-key']);

        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
    }

    public function test_forgot_password_fails_with_invalid_turnstile_token(): void
    {
        config(['services.turnstile.secret_key' => 'test-secret-key']);

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
    }
}
