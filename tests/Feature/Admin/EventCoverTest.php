<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventCoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_replace_and_remove_cover_without_changing_other_event_data(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());
        $payload = ['package_id' => Package::factory()->create()->id, 'category' => 'wedding', 'party_one_name' => 'Anisa', 'party_two_name' => 'Rizky', 'gallery_visibility' => 'public'];
        $this->post('/events', [...$payload, 'cover' => UploadedFile::fake()->image('cover.jpg')])->assertSessionHasNoErrors();
        $event = Event::firstOrFail();
        $original = $event->cover_path;
        Storage::disk('public')->assertExists($original);
        $this->get("/events/{$event->id}/edit")->assertInertia(fn ($page) => $page
            ->where('event.cover_url', asset('storage/'.$original))->missing('event.owner_access_token')
            ->where('ownerUrl', route('event.owner', ['event' => $event, 'token' => $event->owner_access_token])));
        $this->post("/events/{$event->id}", [...$payload, '_method' => 'put', 'status' => 'active', 'cover' => UploadedFile::fake()->image('new.jpg')])->assertSessionHasNoErrors();
        $event->refresh();
        $replacement = $event->cover_path;
        Storage::disk('public')->assertMissing($original);
        Storage::disk('public')->assertExists($replacement);
        $this->get(route('event.show', $event))->assertInertia(fn ($page) => $page->where('event.cover_url', asset('storage/'.$replacement)));
        $this->post("/events/{$event->id}", [...$payload, '_method' => 'put', 'status' => 'active', 'remove_cover' => true])->assertSessionHasNoErrors();
        $this->assertNull($event->fresh()->cover_path);
        $this->assertSame('Anisa', $event->fresh()->party_one_name);
        Storage::disk('public')->assertMissing($replacement);
    }

    public function test_cover_rejects_non_images_and_oversized_uploads(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());
        $payload = ['package_id' => Package::factory()->create()->id, 'category' => 'wedding', 'party_one_name' => 'Anisa', 'gallery_visibility' => 'public'];
        foreach ([UploadedFile::fake()->create('file.pdf', 5, 'application/pdf'), UploadedFile::fake()->image('large.jpg')->size(8193)] as $file) {
            $this->post('/events', [...$payload, 'cover' => $file])->assertSessionHasErrors('cover');
        }
        $this->assertDatabaseCount('events', 0);
    }
}
