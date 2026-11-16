<?php

namespace Tests\Feature;

use App\Events\ContributionCreated;
use App\Events\ContributionDeleted;
use App\Events\ContributionUpdated;
use App\Jobs\ProcessContributionMedia;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContributionBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_contribution_broadcasts_contribution_created_event(): void
    {
        EventFacade::fake([ContributionCreated::class]);
        Queue::fake();

        $event = Event::factory()->create();

        $this->post(route('event.contributions.store', $event), [
            'type' => 'wish',
            'guest_name' => 'Dimas & Anisa',
            'visibility' => 'public',
            'message' => 'Selamat menempuh hidup baru!',
        ])->assertSessionHasNoErrors();

        EventFacade::assertDispatched(ContributionCreated::class, function ($e) use ($event) {
            $channels = array_map(fn ($c) => $c->name, $e->broadcastOn());

            return in_array('events.'.$event->slug, $channels, true)
                && in_array('events.'.$event->id, $channels, true)
                && $e->broadcastAs() === 'ContributionCreated'
                && $e->contribution->guest_name === 'Dimas & Anisa'
                && $e->contribution->message === 'Selamat menempuh hidup baru!';
        });
    }

    public function test_client_toggle_contribution_broadcasts_contribution_updated_event(): void
    {
        EventFacade::fake([ContributionUpdated::class]);

        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);
        $contribution = Contribution::factory()->create([
            'event_id' => $event->id,
            'is_approved' => true,
        ]);

        $this->actingAs($user)
            ->patch(route('client.events.contributions.toggle', [$event, $contribution]))
            ->assertRedirect();

        EventFacade::assertDispatched(ContributionUpdated::class, function ($e) use ($contribution) {
            return $e->contribution->id === $contribution->id
                && $e->contribution->is_approved === false;
        });
    }

    public function test_client_destroy_contribution_broadcasts_contribution_deleted_event(): void
    {
        EventFacade::fake([ContributionDeleted::class]);

        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);
        $contribution = Contribution::factory()->create([
            'event_id' => $event->id,
        ]);

        $contributionId = $contribution->id;

        $this->actingAs($user)
            ->delete(route('client.events.contributions.destroy', [$event, $contribution]))
            ->assertRedirect();

        EventFacade::assertDispatched(ContributionDeleted::class, function ($e) use ($contributionId, $event) {
            $channels = array_map(fn ($c) => $c->name, $e->broadcastOn());

            return $e->contributionId === $contributionId
                && in_array('events.'.$event->slug, $channels, true)
                && $e->broadcastWith()['id'] === $contributionId;
        });
    }

    public function test_photo_processing_broadcasts_contribution_updated(): void
    {
        EventFacade::fake([ContributionUpdated::class]);
        Storage::fake('r2');

        $event = Event::factory()->create();
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        $path = $file->store("photobooth/events/{$event->id}/photo", 'r2');

        $contribution = Contribution::factory()->create([
            'event_id' => $event->id,
            'type' => 'photo',
            'file_disk' => 'r2',
            'file_path' => $path,
            'processing_status' => 'pending',
            'is_approved' => true,
        ]);

        (new ProcessContributionMedia($contribution))->handle();

        EventFacade::assertDispatched(ContributionUpdated::class, function ($e) use ($contribution) {
            return $e->contribution->id === $contribution->id
                && $e->contribution->processing_status === 'done';
        });
    }
}
