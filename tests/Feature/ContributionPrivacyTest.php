<?php

namespace Tests\Feature;

use App\Enums\GalleryVisibility;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContributionPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_and_visibility_are_required_for_every_submission_type(): void
    {
        $event = Event::factory()->create();
        foreach (['photo', 'video', 'voice', 'wish'] as $type) {
            $this->post(route('event.contributions.store', $event), [
                'type' => $type, 'guest_name' => '   ', 'message' => 'Selamat!',
            ])->assertSessionHasErrors(['guest_name', 'visibility']);
        }
        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_private_uploads_are_saved_outside_public_storage_and_hidden_from_guests(): void
    {
        Storage::fake('r2');
        Storage::fake('public');
        $event = Event::factory()->create();
        $this->post(route('event.contributions.store', $event), [
            'type' => 'photo', 'layout' => 2, 'guest_name' => 'Budi', 'visibility' => 'private',
            'file' => UploadedFile::fake()->image('photobox.jpg'),
        ])->assertSessionHasNoErrors();

        $photo = $event->contributions()->firstOrFail();
        $this->assertSame('private', $photo->visibility);
        $this->assertSame('r2', $photo->file_disk);
        Storage::disk('r2')->assertExists($photo->file_path);
        Storage::disk('public')->assertMissing($photo->file_path);
        $mediaUrl = route('contributions.media', $photo);
        $this->get($mediaUrl)->assertNotFound();
        $this->get($mediaUrl.'?download=1')->assertNotFound();
        $this->get(route('event.section', ['event' => $event, 'section' => 'album']))
            ->assertInertia(fn ($page) => $page->where('canViewPrivate', false)->has('contributions.data', 0)->missing('event.owner_access_token'));
        $this->actingAs(User::factory()->create())->get($mediaUrl)->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())->get($mediaUrl)->assertRedirect($photo->file_url);
    }

    public function test_public_feed_excludes_private_and_moderated_items_for_all_media_types(): void
    {
        $event = Event::factory()->create();
        $visible = Contribution::factory()->for($event)->create(['visibility' => 'public']);
        foreach (['photo', 'video', 'voice', 'wish'] as $type) {
            Contribution::factory()->for($event)->create(['type' => $type, 'visibility' => 'private']);
            Contribution::factory()->for($event)->create(['type' => $type, 'visibility' => 'public', 'is_approved' => false]);
        }
        $this->get(route('event.gallery', $event))->assertInertia(fn ($page) => $page
            ->has('contributions.data', 1)->where('contributions.data.0.id', $visible->id)->missing('contributions.data.0.file_path'));
    }

    public function test_owner_link_grants_access_only_to_its_event_and_keeps_token_out_of_guest_payload(): void
    {
        Storage::fake('local');
        $event = Event::factory()->create(['gallery_visibility' => GalleryVisibility::Private]);
        $other = Event::factory()->create();
        Storage::disk('local')->put('private.jpg', 'private photo');
        Storage::disk('local')->put('thumb.jpg', 'private thumbnail');
        $photo = Contribution::factory()->for($event)->create([
            'visibility' => 'private', 'file_path' => 'private.jpg', 'thumbnail_path' => 'thumb.jpg', 'file_disk' => 'local',
        ]);
        $otherPhoto = Contribution::factory()->for($other)->create(['visibility' => 'private', 'file_path' => 'private.jpg', 'file_disk' => 'local']);
        $this->get(route('event.owner', ['event' => $event, 'token' => 'wrong']))->assertNotFound();
        $this->get($photo->thumbnail_url)->assertNotFound();
        $this->get(route('event.owner', ['event' => $event, 'token' => $event->owner_access_token]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('canViewPrivate', true)
                ->where('canViewGallery', true)
                ->where('ownerToken', $event->owner_access_token)
                ->has('contributions.data', 1)
                ->missing('event.owner_access_token'));
        // Guest route on private event never displays private contributions:
        $this->get(route('event.section', ['event' => $event, 'section' => 'album']))
            ->assertInertia(fn ($page) => $page
                ->where('canViewPrivate', false)
                ->where('canViewGallery', false)
                ->has('contributions', 0));
        $this->get(route('event.gallery', $event))->assertInertia(fn ($page) => $page
            ->where('canViewPrivate', true)->has('contributions.data', 1)->missing('event.owner_access_token'));
        $this->get($photo->file_url)->assertOk();
        $this->get($photo->thumbnail_url)->assertOk();
        $this->get($photo->file_url.'?download=1')->assertDownload('private.jpg');
        $this->get($otherPhoto->file_url)->assertNotFound();
        $photo->update(['is_approved' => false]);
        $this->get($photo->file_url)->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())->get($photo->file_url)->assertOk();
    }

    public function test_private_event_cannot_publish_a_submission_even_when_client_sends_public(): void
    {
        $event = Event::factory()->create(['gallery_visibility' => GalleryVisibility::Private]);
        $this->post(route('event.contributions.store', $event), [
            'type' => 'wish', 'guest_name' => 'Budi', 'message' => 'Untuk kalian.', 'visibility' => 'public',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('contributions', ['event_id' => $event->id, 'visibility' => 'private']);
    }

    public function test_media_respects_approval_and_event_privacy_and_serves_the_original_file(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('photobox.jpg');
        Storage::disk('local')->put('photobox.jpg', $file->getContent());
        $photo = Contribution::factory()->create(['file_path' => 'photobox.jpg', 'file_disk' => 'local', 'visibility' => 'public']);
        $response = $this->get($photo->file_url)->assertOk();
        $this->assertSame(hash_file('sha256', Storage::disk('local')->path('photobox.jpg')), hash_file('sha256', $response->baseResponse->getFile()->getPathname()));
        $this->get($photo->file_url.'?download=1')->assertDownload('photobox.jpg');
        $photo->update(['is_approved' => false]);
        $this->get($photo->file_url)->assertNotFound();
        $photo->update(['is_approved' => true]);
        $photo->event->update(['gallery_visibility' => GalleryVisibility::Private]);
        $this->get($photo->file_url)->assertNotFound();
    }
}
