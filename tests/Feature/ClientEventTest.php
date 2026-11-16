<?php

namespace Tests\Feature;

use App\Enums\ContributionType;
use App\Enums\EventStatus;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\Package;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_client_can_view_own_event_management_page(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $event = Event::factory()->create(['user_id' => $client->id]);

        $response = $this->actingAs($client)->get(route('client.events.show', $event));

        $response->assertOk();
    }

    public function test_client_visiting_events_index_renders_table(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        Event::factory()->count(2)->create(['user_id' => $client->id]);

        $response = $this->actingAs($client)->get(route('client.events.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('client/events/Index')
            ->has('userEvents', 2)
        );
    }

    public function test_non_admin_client_cannot_view_another_clients_event(): void
    {
        $clientA = User::factory()->create(['is_admin' => false]);
        $clientB = User::factory()->create(['is_admin' => false]);
        $eventA = Event::factory()->create(['user_id' => $clientA->id]);

        $response = $this->actingAs($clientB)->get(route('client.events.show', $eventA));

        $response->assertForbidden();
    }

    public function test_client_can_update_event_details_but_cannot_change_package_or_status(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $originalPackage = Package::factory()->create(['name' => 'Original Package']);
        $otherPackage = Package::factory()->create(['name' => 'Other Package']);
        $theme = Theme::factory()->create();

        $event = Event::factory()->create([
            'user_id' => $client->id,
            'package_id' => $originalPackage->id,
            'status' => EventStatus::Draft,
            'party_one_name' => 'Adit',
            'party_two_name' => 'Bunga',
        ]);

        $originalDate = $event->event_date?->toDateString();

        $response = $this->actingAs($client)->put(route('client.events.update', $event), [
            'party_one_name' => 'Aditya Pratama',
            'party_two_name' => 'Bunga Lestari',
            'event_date' => '2026-12-25',
            'venue' => 'Gedung Kesenian',
            'notes' => 'Selamat datang semuanya!',
            'gallery_visibility' => 'private',
            'theme_id' => $theme->id,
            'package_id' => $otherPackage->id, // Attempt to tamper package
            'status' => 'active', // Attempt to tamper status
        ]);

        $response->assertSessionHasNoErrors();
        $event->refresh();

        $this->assertSame('Aditya Pratama', $event->party_one_name);
        $this->assertSame('Bunga Lestari', $event->party_two_name);
        $this->assertSame('Gedung Kesenian', $event->venue);
        $this->assertSame('Selamat datang semuanya!', $event->notes);
        // Ensure package, event_date, and status were NOT modified by client
        $this->assertSame($originalPackage->id, $event->package_id);
        $this->assertSame(EventStatus::Draft, $event->status);
        $this->assertSame($originalDate, $event->event_date?->toDateString());
    }

    public function test_client_can_toggle_contribution_visibility(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $event = Event::factory()->create(['user_id' => $client->id]);
        $contribution = Contribution::factory()->create([
            'event_id' => $event->id,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($client)->patch(
            route('client.events.contributions.toggle', ['event' => $event, 'contribution' => $contribution])
        );

        $response->assertRedirect();
        $this->assertFalse($contribution->fresh()->is_approved);
    }

    public function test_client_can_delete_contribution(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $event = Event::factory()->create(['user_id' => $client->id]);
        $contribution = Contribution::factory()->create([
            'event_id' => $event->id,
        ]);

        $response = $this->actingAs($client)->delete(
            route('client.events.contributions.destroy', ['event' => $event, 'contribution' => $contribution])
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('contributions', ['id' => $contribution->id]);
    }

    public function test_client_cannot_download_zip_if_package_disallows(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $package = Package::factory()->create(['allow_zip_download' => false]);
        $event = Event::factory()->create([
            'user_id' => $client->id,
            'package_id' => $package->id,
        ]);

        $response = $this->actingAs($client)->get(route('client.events.download-zip', $event));

        $response->assertForbidden();
    }

    public function test_client_can_download_zip_if_package_allows(): void
    {
        Storage::fake('public');
        $client = User::factory()->create(['is_admin' => false]);
        $package = Package::factory()->create(['allow_zip_download' => true]);
        $event = Event::factory()->create([
            'user_id' => $client->id,
            'package_id' => $package->id,
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('contributions', 'public');

        Contribution::factory()->create([
            'event_id' => $event->id,
            'file_path' => $path,
            'is_approved' => true,
            'type' => ContributionType::Photo,
        ]);

        $response = $this->actingAs($client)->get(route('client.events.download-zip', $event));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');
    }

    public function test_event_automatically_sets_expires_at_based_on_package_gallery_days(): void
    {
        $package = Package::factory()->create(['gallery_days' => 45]);
        $event = Event::factory()->create([
            'package_id' => $package->id,
            'event_date' => '2026-10-01',
            'expires_at' => null,
        ]);

        $this->assertNotNull($event->expires_at);
        $this->assertSame('2026-11-15', $event->expires_at->toDateString());
    }

    public function test_admin_can_assign_user_id_when_creating_and_updating_event(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $client = User::factory()->create(['is_admin' => false]);
        $package = Package::factory()->create();

        $storeResponse = $this->actingAs($admin)->post(route('events.store'), [
            'user_id' => $client->id,
            'package_id' => $package->id,
            'category' => 'wedding',
            'party_one_name' => 'Ferry',
            'party_two_name' => 'Indah',
            'gallery_visibility' => 'public',
        ]);

        $storeResponse->assertRedirect();
        $event = Event::where('party_one_name', 'Ferry')->firstOrFail();
        $this->assertSame($client->id, $event->user_id);

        $otherClient = User::factory()->create(['is_admin' => false]);
        $updateResponse = $this->actingAs($admin)->put(route('events.update', $event), [
            'user_id' => $otherClient->id,
            'package_id' => $package->id,
            'status' => 'active',
            'category' => 'wedding',
            'party_one_name' => 'Ferry',
            'party_two_name' => 'Indah',
            'gallery_visibility' => 'public',
        ]);

        $updateResponse->assertRedirect();
        $this->assertSame($otherClient->id, $event->fresh()->user_id);
    }

    public function test_client_on_basic_package_cannot_apply_custom_theme_or_custom_colors(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $basic = Package::factory()->create([
            'slug' => 'basic',
            'allow_custom_theme' => false,
            'max_frames' => 1,
            'allow_zip_download' => false,
        ]);
        $theme = Theme::factory()->create([
            'slug' => 'matcha',
            'accent_color' => '#9bb86a',
        ]);
        $event = Event::factory()->for($basic)->create([
            'user_id' => $client->id,
            'status' => EventStatus::Active,
            'theme_id' => null,
            'custom_colors' => null,
        ]);

        $this->actingAs($client)
            ->put(route('client.events.update', $event), [
                'party_one_name' => 'Dimas',
                'party_two_name' => 'Wulan',
                'gallery_visibility' => 'public',
                'theme_id' => $theme->id,
                'use_custom_colors' => true,
                'custom_colors' => [
                    'primary' => '#112233',
                    'accent' => '#445566',
                    'neutral' => '#fafafa',
                ],
            ])
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertNull($event->theme_id);
        $this->assertNull($event->custom_colors);

        $this->get(route('event.show', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('event.theme', 'titip-momen')
                ->where('event.colors.accent', '#ff5d6c'));
    }

    public function test_client_on_plus_package_can_apply_preset_theme_and_custom_colors(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $plus = Package::factory()->create([
            'slug' => 'plus',
            'allow_custom_theme' => true,
            'max_frames' => 2,
            'allow_zip_download' => true,
        ]);
        $theme = Theme::factory()->create([
            'slug' => 'matcha',
            'primary_color' => '#1c2b22',
            'accent_color' => '#9bb86a',
            'neutral_color' => '#f4f7f1',
        ]);
        $event = Event::factory()->for($plus)->create([
            'user_id' => $client->id,
            'status' => EventStatus::Active,
        ]);

        $this->actingAs($client)
            ->put(route('client.events.update', $event), [
                'party_one_name' => 'Dimas',
                'party_two_name' => 'Wulan',
                'gallery_visibility' => 'public',
                'theme_id' => $theme->id,
                'use_custom_colors' => false,
            ])
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame($theme->id, $event->theme_id);
        $this->assertNull($event->custom_colors);

        $this->get(route('event.show', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('event.theme', 'matcha')
                ->where('event.colors.accent', '#9bb86a'));

        $this->actingAs($client)
            ->put(route('client.events.update', $event), [
                'party_one_name' => 'Dimas',
                'party_two_name' => 'Wulan',
                'gallery_visibility' => 'public',
                'theme_id' => $theme->id,
                'use_custom_colors' => true,
                'custom_colors' => [
                    'primary' => '#2b1b2e',
                    'accent' => '#c86dd7',
                    'neutral' => '#faf5fc',
                ],
            ])
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame([
            'primary' => '#2b1b2e',
            'accent' => '#c86dd7',
            'neutral' => '#faf5fc',
        ], $event->custom_colors);

        $this->get(route('event.show', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('event.colors.primary', '#2b1b2e')
                ->where('event.colors.accent', '#c86dd7'));
    }

    public function test_client_show_displays_allowed_photo_layouts_within_package_limit(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $basic = Package::factory()->create(['max_frames' => 1]);
        $event = Event::factory()->for($basic)->create(['user_id' => $client->id]);

        $this->actingAs($client)
            ->get(route('client.events.show', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('client/events/Show')
                ->where('allowedLayouts', [1]));
    }
}
