<?php

namespace Tests\Feature;

use App\Enums\GalleryVisibility;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\Package;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_the_event_page(): void
    {
        $event = Event::factory()->create();

        $this->get(route('event.show', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('event/Show')
                ->where('isOpen', true)
                ->where('event.slug', $event->slug)
                ->where('event.notes', $event->notes));
    }

    public function test_guest_can_upload_a_photo(): void
    {
        Storage::fake('r2');
        $event = Event::factory()->create();

        $this->post(route('event.contributions.store', $event), [
            'type' => 'photo',
            'layout' => 2,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->image('p.jpg'),
        ])->assertSessionHasNoErrors();

        $contribution = $event->contributions()->first();
        $this->assertSame('photo', $contribution->type->value);
        $this->assertSame('r2', $contribution->file_disk);
        $this->assertStringStartsWith("photobooth/events/{$event->id}/photo/", $contribution->file_path);
        $this->assertTrue(Storage::disk('r2')->exists($contribution->file_path));
    }

    public function test_menu_urls_render_the_requested_section_on_first_load(): void
    {
        $event = Event::factory()->create();

        foreach (['album' => 'album', 'reels' => 'reels', 'catatan' => 'notes', 'acara' => 'info', 'titip' => 'album'] as $section => $tab) {
            $this->get(route('event.section', ['event' => $event, 'section' => $section]))
                ->assertOk()
                ->assertInertia(fn ($page) => $page
                    ->component('event/Show')
                    ->where('navigation.section', $section)
                    ->where('navigation.tab', $tab));
        }

        $this->get(route('event.show', $event))->assertInertia(fn ($page) => $page
            ->where('navigation.section', 'cover'));
        $this->get(route('event.gallery', $event))->assertInertia(fn ($page) => $page
            ->where('navigation.section', 'album'));
        $this->get("/e/{$event->slug}/unknown")->assertNotFound();
    }

    public function test_menu_urls_restore_filters_layout_and_composer_origin(): void
    {
        $event = Event::factory()->create();

        $this->get("/e/{$event->slug}/album?view=grid")->assertInertia(fn ($page) => $page
            ->where('navigation.view', 'grid'));
        $this->get("/e/{$event->slug}/catatan?filter=voice")->assertInertia(fn ($page) => $page
            ->where('navigation.filter', 'voice'));
        $this->get("/e/{$event->slug}/titip?from=catatan&filter=wish")->assertInertia(fn ($page) => $page
            ->where('navigation.section', 'titip')
            ->where('navigation.tab', 'notes')
            ->where('navigation.filter', 'wish'));
        $this->get("/e/{$event->slug}/titip?from[]=unknown&filter[]=unknown&view=unknown")
            ->assertInertia(fn ($page) => $page
                ->where('navigation.tab', 'album')
                ->where('navigation.filter', 'all')
                ->where('navigation.view', 'feed'));
    }

    public function test_direct_menu_urls_keep_private_contributions_hidden(): void
    {
        $event = Event::factory()->create(['gallery_visibility' => GalleryVisibility::Private]);
        Contribution::factory()->for($event)->create();

        foreach (['album', 'reels', 'catatan', 'acara', 'titip'] as $section) {
            $this->get(route('event.section', ['event' => $event, 'section' => $section]))
                ->assertOk()
                ->assertInertia(fn ($page) => $page
                    ->where('canViewGallery', false)
                    ->has('contributions', 0));
        }
    }

    public function test_guest_can_leave_a_wish(): void
    {
        $event = Event::factory()->create();

        $this->post(route('event.contributions.store', $event), [
            'type' => 'wish',
            'message' => 'Selamat menempuh hidup baru!',
            'guest_name' => 'Budi', 'visibility' => 'public',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contributions', ['event_id' => $event->id, 'type' => 'wish']);
    }

    public function test_photo_requires_a_valid_layout_and_image(): void
    {
        Storage::fake('public');
        $event = Event::factory()->create();

        $this->post(route('event.contributions.store', $event), [
            'type' => 'photo',
            'layout' => 9,
            'file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['layout', 'file']);

        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_cannot_upload_to_draft_or_expired_events(): void
    {
        $draft = Event::factory()->draft()->create();
        $expired = Event::factory()->expired()->create();

        foreach ([$draft, $expired] as $event) {
            $this->post(route('event.contributions.store', $event), [
                'type' => 'wish',
                'message' => 'hi',
            ])->assertForbidden();
        }
    }

    public function test_private_gallery_is_admin_only(): void
    {
        $event = Event::factory()->create(['gallery_visibility' => GalleryVisibility::Private]);
        Contribution::factory()->for($event)->create();

        $this->get(route('event.gallery', $event))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('event.gallery', $event))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('event.gallery', $event))->assertOk();
    }

    public function test_public_gallery_lists_only_approved_contributions(): void
    {
        $event = Event::factory()->create();
        Contribution::factory()->for($event)->create();
        Contribution::factory()->for($event)->create(['is_approved' => false]);

        $this->get(route('event.gallery', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('event/Gallery')->has('contributions.data', 1));
    }

    public function test_home_feed_exposes_only_approved_contributions(): void
    {
        $event = Event::factory()->create();
        $approved = Contribution::factory()->for($event)->create();
        Contribution::factory()->for($event)->create(['is_approved' => false]);
        Contribution::factory()->create();

        $this->get(route('event.show', $event))->assertInertia(fn ($page) => $page
            ->where('canViewGallery', true)
            ->has('contributions.data', 1)
            ->where('contributions.data.0.id', $approved->id));
    }

    public function test_private_contributions_are_not_exposed_on_the_event_home(): void
    {
        $event = Event::factory()->create(['gallery_visibility' => GalleryVisibility::Private]);
        Contribution::factory()->for($event)->create(['message' => 'Private message']);

        $this->get(route('event.show', $event))->assertInertia(fn ($page) => $page
            ->where('canViewGallery', false)->has('contributions', 0));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('event.show', $event))->assertInertia(fn ($page) => $page
            ->where('canViewGallery', true)->has('contributions.data', 1));
    }

    public function test_theme_and_package_capabilities_are_shared_by_both_guest_pages(): void
    {
        $theme = Theme::factory()->create(['slug' => 'matcha', 'accent_color' => '#9bb86a']);
        $package = Package::factory()->create([
            'max_frames' => 2,
            'allow_custom_theme' => true,
            'allow_video' => true,
            'max_video_seconds' => 20,
            'max_video_kb' => 15360,
        ]);
        $event = Event::factory()->for($theme)->for($package)->create();

        foreach (['event.show', 'event.gallery'] as $route) {
            $this->get(route($route, $event))->assertInertia(fn ($page) => $page
                ->where('event.theme', 'matcha')
                ->where('event.colors.accent', '#9bb86a')
                ->where('event.capabilities.max_frames', 2)
                ->where('event.capabilities.types', ['photo', 'video', 'voice', 'wish'])
                ->where('event.capabilities.photo_layouts', [1, 2])
                ->where('event.capabilities.max_video_seconds', 20)
                ->where('event.capabilities.max_video_kb', 15360)
                ->where('event.capabilities.max_photo_kb', 4096));
        }
    }

    public function test_basic_package_limits_photo_layout_to_one_and_disables_video(): void
    {
        Storage::fake('r2');
        $basic = Package::factory()->create([
            'max_frames' => 1,
            'allow_video' => false,
            'max_video_seconds' => 0,
            'max_video_kb' => 0,
        ]);
        $event = Event::factory()->for($basic)->create();

        $this->get(route('event.show', $event))->assertInertia(fn ($page) => $page
            ->where('frames', [])
            ->where('event.capabilities.photo_layouts', [1])
            ->where('event.capabilities.types', ['photo', 'voice', 'wish'])
            ->where('event.capabilities.max_video_seconds', 0));

        // Direct visit to /reels on Basic package falls back to album
        $this->get(route('event.show', ['event' => $event, 'section' => 'reels']))
            ->assertInertia(fn ($page) => $page->where('navigation.tab', 'album'));

        // Layout 1 succeeds
        $this->post(route('event.contributions.store', $event), [
            'type' => 'photo',
            'layout' => 1,
            'guest_name' => 'Ani',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->image('pose1.jpg'),
        ])->assertSessionHasNoErrors();

        // Layout 2 is rejected
        $this->post(route('event.contributions.store', $event), [
            'type' => 'photo',
            'layout' => 2,
            'guest_name' => 'Ani',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->image('pose2.jpg'),
        ])->assertSessionHasErrors('layout');

        // Custom frame_id is always rejected
        $this->post(route('event.contributions.store', $event), [
            'type' => 'photo',
            'layout' => 1,
            'frame_id' => 'custom-frame-id',
            'guest_name' => 'Ani',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->image('framed.jpg'),
        ])->assertSessionHasErrors('frame_id');

        // Video is disabled on Basic
        $this->post(route('event.contributions.store', $event), [
            'type' => 'video',
            'duration_seconds' => 10,
            'guest_name' => 'Ani',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('clip.mp4', 512, 'video/mp4'),
        ])->assertSessionHasErrors('type');

        $this->assertDatabaseCount('contributions', 1);
    }

    public function test_plus_and_full_packages_enforce_layout_caps_and_tier_file_and_duration_limits(): void
    {
        Storage::fake('r2');
        $plus = Package::factory()->create([
            'max_frames' => 2,
            'allow_video' => true,
            'max_video_seconds' => 20,
            'max_video_kb' => 15360,
        ]);
        $plusEvent = Event::factory()->for($plus)->create();

        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'photo',
            'layout' => 2,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->image('plus2.jpg'),
        ])->assertSessionHasNoErrors();

        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'photo',
            'layout' => 3,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->image('plus3.jpg'),
        ])->assertSessionHasErrors('layout');

        // Photo > 4096 KB rejected
        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'photo',
            'layout' => 1,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('huge.jpg', 5000, 'image/jpeg'),
        ])->assertSessionHasErrors('file');

        // Video 20s succeeds, 21s & >15360 KB rejected
        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'video',
            'duration_seconds' => 20,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('ok.mp4', 1024, 'video/mp4'),
        ])->assertSessionHasNoErrors();

        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'video',
            'duration_seconds' => 21,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('too-long.mp4', 1024, 'video/mp4'),
        ])->assertSessionHasErrors('duration_seconds');

        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'video',
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('no-duration.mp4', 1024, 'video/mp4'),
        ])->assertSessionHasErrors('duration_seconds');

        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'voice',
            'duration_seconds' => 61,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('voice-long.webm', 512, 'audio/webm'),
        ])->assertSessionHasErrors('duration_seconds');

        $this->post(route('event.contributions.store', $plusEvent), [
            'type' => 'video',
            'duration_seconds' => 10,
            'guest_name' => 'Budi',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('too-big.mp4', 16000, 'video/mp4'),
        ])->assertSessionHasErrors('file');

        // Full tier allows layout 4 and 45s video
        $full = Package::factory()->create([
            'max_frames' => 4,
            'allow_video' => true,
            'max_video_seconds' => 45,
            'max_video_kb' => 25600,
        ]);
        $fullEvent = Event::factory()->for($full)->create();

        $this->post(route('event.contributions.store', $fullEvent), [
            'type' => 'photo',
            'layout' => 4,
            'guest_name' => 'Cici',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->image('quad.jpg'),
        ])->assertSessionHasNoErrors();

        $this->post(route('event.contributions.store', $fullEvent), [
            'type' => 'video',
            'duration_seconds' => 45,
            'guest_name' => 'Cici',
            'visibility' => 'public',
            'file' => UploadedFile::fake()->create('full45.mp4', 20000, 'video/mp4'),
        ])->assertSessionHasNoErrors();
    }

    public function test_photo_caption_is_saved_and_empty_wishes_are_rejected(): void
    {
        Storage::fake('r2');
        $event = Event::factory()->create();
        $this->post(route('event.contributions.store', $event), [
            'type' => 'photo', 'layout' => 1, 'message' => 'A shared memory',
            'guest_name' => 'Budi', 'visibility' => 'public',
            'file' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('contributions', ['event_id' => $event->id, 'message' => 'A shared memory']);

        $this->post(route('event.contributions.store', $event), [
            'type' => 'wish', 'message' => '',
        ])->assertSessionHasErrors('message');
    }

    public function test_guest_can_submit_video_and_voice_recordings(): void
    {
        Storage::fake('r2');
        $event = Event::factory()->create();
        foreach (['video' => 'video/webm', 'voice' => 'audio/webm'] as $type => $mime) {
            $this->post(route('event.contributions.store', $event), [
                'type' => $type, 'duration_seconds' => 12,
                'guest_name' => 'Budi', 'visibility' => 'public',
                'file' => UploadedFile::fake()->create('recording.webm', 20, $mime),
            ])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('contributions', ['event_id' => $event->id, 'type' => $type, 'duration_seconds' => 12]);
        }
    }

    public function test_real_wav_upload_accepts_the_mime_detected_by_the_server(): void
    {
        Storage::fake('r2');
        $event = Event::factory()->create();
        $samples = str_repeat(pack('v', 0), 16000);
        $wav = 'RIFF'.pack('V', 36 + strlen($samples)).'WAVEfmt '
            .pack('VvvVVvv', 16, 1, 1, 16000, 32000, 2, 16)
            .'data'.pack('V', strlen($samples)).$samples;

        $this->post(route('event.contributions.store', $event), [
            'type' => 'voice', 'duration_seconds' => 1,
            'guest_name' => 'Budi', 'visibility' => 'public',
            'file' => UploadedFile::fake()->createWithContent('voice.wav', $wav),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contributions', ['event_id' => $event->id, 'type' => 'voice']);
    }

    public function test_html_head_enforces_notranslate_and_light_color_scheme(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('event.show', $event));

        $response->assertOk();
        $response->assertSee('translate="no"', false);
        $response->assertSee('class="notranslate"', false);
        $response->assertSee('<meta name="google" content="notranslate">', false);
        $response->assertSee('<meta name="color-scheme" content="light">', false);
    }

    public function test_post_too_large_exception_is_handled_gracefully(): void
    {
        Route::middleware('web')->post('/test-oversized-upload', function () {
            throw new PostTooLargeException;
        });

        $response = $this->from('/e/test/titip')->post('/test-oversized-upload', []);

        $response->assertRedirect('/e/test/titip');
        $response->assertSessionHasErrors('file');
    }

    public function test_post_too_large_exception_returns_json_when_expected(): void
    {
        Route::post('/test-oversized-upload-json', function () {
            throw new PostTooLargeException;
        });

        $response = $this->postJson('/test-oversized-upload-json', []);

        $response->assertStatus(413);
        $response->assertJsonStructure(['message']);
    }
}
