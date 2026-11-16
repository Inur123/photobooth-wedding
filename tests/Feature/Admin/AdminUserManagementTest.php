<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_user_management(): void
    {
        $client = User::factory()->create(['is_admin' => false]);

        $this->actingAs($client)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_accessing_user_management(): void
    {
        $this->get('/users')
            ->assertRedirect('/login');
    }

    public function test_admin_can_view_users_list_and_stats(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Super Admin']);
        $activeClient = User::factory()->create(['name' => 'Budi Santoso', 'is_active' => true]);
        $inactiveClient = User::factory()->inactive()->create(['name' => 'Dewi Lestari']);

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 3)
                ->where('stats.total', 3)
                ->where('stats.active', 2)
                ->where('stats.inactive', 1));
    }

    public function test_admin_can_filter_and_search_users(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Utama', 'email' => 'admin@test.com']);
        $client1 = User::factory()->create(['name' => 'Ahmad Dani', 'email' => 'ahmad@test.com', 'is_active' => true]);
        $client2 = User::factory()->inactive()->create(['name' => 'Siti Aminah', 'email' => 'siti@test.com']);

        // Search by name
        $this->actingAs($admin)
            ->get('/users?search=Ahmad')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Ahmad Dani'));

        // Filter inactive
        $this->actingAs($admin)
            ->get('/users?filter=inactive')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Siti Aminah'));
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['is_active' => true]);

        // Deactivate client
        $this->actingAs($admin)
            ->patch("/users/{$client->id}/status")
            ->assertRedirect();

        $this->assertFalse($client->fresh()->is_active);

        // Reactivate client
        $this->actingAs($admin)
            ->patch("/users/{$client->id}/status")
            ->assertRedirect();

        $this->assertTrue($client->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch("/users/{$admin->id}/status")
            ->assertRedirect();

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_toggle_email_verification_manually(): void
    {
        $admin = User::factory()->admin()->create();
        $unverifiedClient = User::factory()->unverified()->create();

        $this->assertNull($unverifiedClient->email_verified_at);

        // Manually verify
        $this->actingAs($admin)
            ->patch("/users/{$unverifiedClient->id}/verify")
            ->assertRedirect();

        $this->assertNotNull($unverifiedClient->fresh()->email_verified_at);

        // Toggle back to unverified
        $this->actingAs($admin)
            ->patch("/users/{$unverifiedClient->id}/verify")
            ->assertRedirect();

        $this->assertNull($unverifiedClient->fresh()->email_verified_at);
    }

    public function test_admin_cannot_toggle_own_email_verification(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);

        $this->actingAs($admin)
            ->patch("/users/{$admin->id}/verify")
            ->assertRedirect();

        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create();

        $this->actingAs($admin)
            ->delete("/users/{$client->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $client->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete("/users/{$admin->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactiveUser = User::factory()->inactive()->create([
            'email' => 'inactive@test.com',
            'password' => 'password123456',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123456',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_user_can_login(): void
    {
        $activeUser = User::factory()->create([
            'email' => 'active@test.com',
            'password' => 'password123456',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'active@test.com',
            'password' => 'password123456',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($activeUser);
    }

    public function test_deactivated_logged_in_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        // Login as user
        $this->actingAs($user);

        // Admin deactivates user in database
        $user->update(['is_active' => false]);

        // User makes next request
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_verified_event_ensures_user_is_active(): void
    {
        $user = User::factory()->unverified()->create(['is_active' => false]);

        event(new Verified($user));

        $this->assertTrue($user->fresh()->is_active);
    }
}
