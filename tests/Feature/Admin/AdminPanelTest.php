<?php

namespace Tests\Feature\Admin;

use App\Enums\EventStatus;
use App\Events\PackageUpdated;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\Package;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_packages_events_and_contributions_using_uuids_and_clean_urls(): void
    {
        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create(['name' => 'Plus', 'price' => 250000]);
        $event = Event::factory()->for($package)->create(['party_one_name' => 'Anisa', 'party_two_name' => 'Rizky']);
        $contribution = Contribution::factory()->for($event)->create(['is_approved' => false]);

        $this->assertTrue(Str::isUuid($admin->id));
        $this->assertTrue(Str::isUuid($package->id));
        $this->assertTrue(Str::isUuid($event->id));
        $this->assertTrue(Str::isUuid($contribution->id));

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Dashboard')
                ->where('stats.total_events', 1)
                ->where('stats.pending_approvals', 1));

        $this->actingAs($admin)
            ->get('/packages')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/packages/Index')
                ->has('packages', 1));

        $this->actingAs($admin)
            ->get("/packages/{$package->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/packages/Edit')
                ->where('package.id', $package->id)
                ->has('coreBenefits', 3));

        $this->actingAs($admin)
            ->put("/packages/{$package->id}", [
                'name' => 'Plus Updated',
                'tagline' => 'Tagline baru',
                'price' => 275000,
                'gallery_days' => 60,
                'max_frames' => 2,
                'allow_zip_download' => true,
                'allow_custom_theme' => true,
                'is_active' => true,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Plus Updated',
            'price' => 275000,
        ]);

        $this->actingAs($admin)
            ->get('/events')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/events/Index')
                ->has('events.data', 1));

        $this->actingAs($admin)
            ->get("/events/{$event->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/events/Edit')
                ->where('event.id', $event->id));

        $this->actingAs($admin)
            ->get('/contributions?filter=hidden')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/contributions/Index')
                ->where('filter', 'hidden')
                ->where('counts.hidden', 1)
                ->has('contributions.data', 1));

        $this->actingAs($admin)
            ->patch("/contributions/{$contribution->id}/approve")
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'is_approved' => true,
        ]);
    }

    public function test_event_slugs_are_generated_automatically_and_remain_unique(): void
    {
        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create();
        $payload = [
            'package_id' => $package->id,
            'theme_id' => null,
            'category' => 'wedding',
            'party_one_name' => 'Anisa',
            'party_two_name' => 'Rizky',
            'event_date' => '2026-11-04',
            'venue' => 'Magetan',
            'notes' => null,
            'gallery_visibility' => 'public',
            'is_demo' => false,
        ];

        $this->actingAs($admin)
            ->post('/events', $payload)
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post('/events', $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('events', ['slug' => 'anisa-rizky-2026']);
        $this->assertDatabaseHas('events', ['slug' => 'anisa-rizky-2026-2']);
    }

    public function test_event_slug_cannot_be_changed_from_the_update_form(): void
    {
        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create();
        $event = Event::factory()->for($package)->create([
            'slug' => 'anisa-rizky-2026',
        ]);

        $this->actingAs($admin)
            ->put("/events/{$event->id}", [
                'package_id' => $package->id,
                'theme_id' => null,
                'slug' => 'slug-yang-dikirim-browser',
                'category' => 'wedding',
                'status' => 'draft',
                'party_one_name' => 'Anisa Baru',
                'party_two_name' => 'Rizky',
                'event_date' => '2026-11-04',
                'venue' => 'Magetan',
                'notes' => null,
                'gallery_visibility' => 'public',
                'is_demo' => false,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'slug' => 'anisa-rizky-2026',
            'party_one_name' => 'Anisa Baru',
        ]);
    }

    public function test_admin_can_update_event_status_to_closed_and_expired(): void
    {
        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create();
        $event = Event::factory()->for($package)->create();

        $basePayload = [
            'package_id' => $package->id,
            'category' => 'wedding',
            'party_one_name' => 'Dimas',
            'party_two_name' => 'Wulan',
            'event_date' => '2026-11-04',
            'gallery_visibility' => 'public',
            'is_demo' => false,
        ];

        $this->actingAs($admin)
            ->put("/events/{$event->id}", [...$basePayload, 'status' => 'closed'])
            ->assertSessionHasNoErrors();

        $this->assertSame(EventStatus::Closed, $event->fresh()->status);

        $this->actingAs($admin)
            ->put("/events/{$event->id}", [...$basePayload, 'status' => 'expired'])
            ->assertSessionHasNoErrors();

        $this->assertSame(EventStatus::Expired, $event->fresh()->status);
    }

    public function test_admin_package_update_toggles_allow_custom_theme(): void
    {
        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create(['allow_custom_theme' => false]);

        $payload = [
            'name' => $package->name,
            'tagline' => $package->tagline,
            'price' => $package->price,
            'gallery_days' => $package->gallery_days,
            'max_frames' => $package->max_frames,
            'allow_zip_download' => false,
            'allow_custom_theme' => true,
            'is_active' => true,
        ];

        $this->actingAs($admin)
            ->put("/packages/{$package->id}", $payload)
            ->assertSessionHasNoErrors();

        $this->assertTrue($package->fresh()->allow_custom_theme);

        $this->actingAs($admin)
            ->put("/packages/{$package->id}", [...$payload, 'allow_custom_theme' => false])
            ->assertSessionHasNoErrors();

        $this->assertFalse($package->fresh()->allow_custom_theme);
    }

    public function test_admin_upgrading_and_downgrading_package_updates_expiry_and_clears_disallowed_theme(): void
    {
        $admin = User::factory()->admin()->create();
        $basic = Package::factory()->create([
            'slug' => 'basic',
            'gallery_days' => 30,
            'allow_custom_theme' => false,
        ]);
        $full = Package::factory()->create([
            'slug' => 'full',
            'gallery_days' => 90,
            'allow_custom_theme' => true,
        ]);
        $theme = Theme::factory()->create();

        $event = Event::factory()->for($basic)->create([
            'event_date' => '2026-10-01',
            'expires_at' => '2026-10-31 23:59:59',
        ]);

        $basePayload = [
            'category' => 'wedding',
            'status' => 'active',
            'party_one_name' => 'Dimas',
            'party_two_name' => 'Wulan',
            'event_date' => '2026-10-01',
            'gallery_visibility' => 'public',
            'is_demo' => false,
        ];

        $this->actingAs($admin)
            ->put("/events/{$event->id}", [
                ...$basePayload,
                'package_id' => $full->id,
                'theme_id' => $theme->id,
                'use_custom_colors' => true,
                'custom_colors' => [
                    'primary' => '#102030',
                    'accent' => '#a0b0c0',
                    'neutral' => '#f0f0f0',
                ],
            ])
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('2026-12-30', $event->expires_at?->toDateString());
        $this->assertSame($theme->id, $event->theme_id);
        $this->assertSame('#a0b0c0', $event->custom_colors['accent'] ?? null);

        $this->actingAs($admin)
            ->put("/events/{$event->id}", [
                ...$basePayload,
                'package_id' => $basic->id,
                'theme_id' => $theme->id,
                'use_custom_colors' => true,
                'custom_colors' => [
                    'primary' => '#102030',
                    'accent' => '#a0b0c0',
                    'neutral' => '#f0f0f0',
                ],
            ])
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('2026-10-31', $event->expires_at?->toDateString());
        $this->assertNull($event->theme_id);
        $this->assertNull($event->custom_colors);
    }

    public function test_admin_package_quotas_and_benefits_follow_regulated_values_and_cannot_be_faked(): void
    {
        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create([
            'gallery_days' => 60,
            'max_frames' => 2,
            'allow_zip_download' => false,
            'allow_custom_theme' => false,
            'allow_video' => false,
            'max_video_seconds' => 20,
            'max_video_kb' => 15360,
        ]);

        $this->actingAs($admin)
            ->put("/packages/{$package->id}", [
                'name' => 'Custom Check',
                'tagline' => 'Uji otomatis',
                'price' => 200000,
                'gallery_days' => 999,
                'max_frames' => 99,
                'allow_zip_download' => true,
                'allow_custom_theme' => false,
                'allow_video' => true,
                'benefits' => ['Gratis fotografer keliling', 'Fitur fiktif'],
                'is_active' => true,
            ])
            ->assertSessionHasNoErrors();

        $fresh = $package->fresh();
        $this->assertSame(60, $fresh->gallery_days);
        $this->assertSame(2, $fresh->max_frames);
        $this->assertTrue($fresh->allow_video);

        $benefits = $fresh->benefits ?? [];
        $this->assertNotContains('Gratis fotografer keliling', $benefits);
        $this->assertNotContains('Tema warna custom (halaman & bingkai)', $benefits);
        $this->assertContains('2 pilihan layout foto (1–2 pose)', $benefits);
        $this->assertContains('Kiriman video (maks 20 dtk), voice note & ucapan', $benefits);
        $this->assertContains('Unduh semua file (ZIP)', $benefits);
        $this->assertContains('Galeri aktif 60 hari', $benefits);
    }

    public function test_admin_event_edit_provides_date_string_and_qr_card_props(): void
    {
        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create();
        $event = Event::factory()->for($package)->create([
            'party_one_name' => 'Aditya Pratama',
            'party_two_name' => 'Rose',
            'event_date' => '2027-01-01',
        ]);

        $this->actingAs($admin)
            ->get("/events/{$event->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/events/Edit')
                ->where('event.event_date', '2027-01-01')
                ->where('guestUrl', route('event.show', $event))
                ->has('eventColors.primary')
                ->has('eventColors.accent')
                ->has('eventColors.neutral'));
    }

    public function test_updating_package_dispatches_package_updated_broadcast_event(): void
    {
        EventFacade::fake([PackageUpdated::class]);

        $admin = User::factory()->admin()->create();
        $package = Package::factory()->create(['name' => 'Original', 'is_active' => true]);

        $this->actingAs($admin)
            ->put("/packages/{$package->id}", [
                'name' => 'Updated Package',
                'tagline' => 'New Tagline',
                'price' => 200000,
                'allow_zip_download' => true,
                'allow_custom_theme' => true,
                'allow_video' => true,
                'is_active' => false,
            ])
            ->assertRedirect();

        EventFacade::assertDispatched(PackageUpdated::class, function (PackageUpdated $event) use ($package) {
            return $event->package?->id === $package->id
                && $event->broadcastAs() === 'PackageUpdated'
                && in_array('packages', array_map(fn ($ch) => $ch->name, $event->broadcastOn()));
        });
    }
}
