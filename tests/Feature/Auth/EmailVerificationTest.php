<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_email(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Tamu Baru',
            'email' => 'baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, QueuedVerifyEmail::class);
    }

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('client.events.index'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)->get(route('verification.notice'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('auth/VerifyEmail'));
    }

    public function test_unverified_admin_cannot_open_admin_pages(): void
    {
        $admin = User::factory()->admin()->unverified()->create();

        $this->actingAs($admin)->get('/contributions')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_email_can_be_verified_with_signed_link(): void
    {
        Event::fake([Verified::class]);
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)->get($url)->assertRedirect();

        Event::assertDispatched(Verified::class);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->actingAs($user->fresh())->get(route('dashboard'))->assertOk();
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('salah@example.com')],
        );

        $this->actingAs($user)->get($url);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post(route('verification.send'))
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, QueuedVerifyEmail::class);
    }
}
