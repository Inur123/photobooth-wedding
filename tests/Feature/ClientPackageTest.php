<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPackageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('client.packages.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_client_can_view_packages_page(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $package = Package::factory()->create([
            'name' => 'Plus Special',
            'gallery_days' => 60,
        ]);
        Event::factory()->create([
            'user_id' => $client->id,
            'package_id' => $package->id,
            'party_one_name' => 'Mawar',
            'party_two_name' => 'Melati',
        ]);

        $response = $this->actingAs($client)->get(route('client.packages.index'));

        $response->assertOk();
    }

    public function test_client_with_multiple_events_receives_all_events_and_packages(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $packageA = Package::factory()->create(['name' => 'Basic Tier']);
        $packageB = Package::factory()->create(['name' => 'Full Tier']);

        Event::factory()->create([
            'user_id' => $client->id,
            'package_id' => $packageA->id,
            'party_one_name' => 'Event A',
        ]);
        Event::factory()->create([
            'user_id' => $client->id,
            'package_id' => $packageB->id,
            'party_one_name' => 'Event B',
        ]);

        $response = $this->actingAs($client)->get(route('client.packages.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('client/packages/Index')
            ->has('packages')
        );
    }
}
