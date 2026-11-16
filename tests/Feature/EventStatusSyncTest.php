<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Events\EventStatusUpdated;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use Tests\TestCase;

class EventStatusSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_past_date_automatically_becomes_closed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 10:00:00', 'Asia/Jakarta'));

        // Acara tanggal 14 November (kemarin) yang dibuat tanpa override manual
        $event = Event::factory()->auto()->create([
            'event_date' => '2026-11-14',
            'status' => EventStatus::Draft,
        ]);

        $this->assertTrue($event->syncStatus());
        $this->assertSame(EventStatus::Closed, $event->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_event_on_the_day_of_event_automatically_becomes_active(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 08:00:00', 'Asia/Jakarta'));

        // Acara tanggal 15 November (hari ini)
        $event = Event::factory()->auto()->create([
            'event_date' => '2026-11-15',
            'status' => EventStatus::Draft,
        ]);

        $this->assertTrue($event->syncStatus());
        $this->assertSame(EventStatus::Active, $event->fresh()->status);
        $this->assertTrue($event->isOpen());

        Carbon::setTestNow();
    }

    public function test_manual_admin_override_prevents_automatic_date_closure(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 10:00:00', 'Asia/Jakarta'));

        // Acara yang tanggalnya kemarin (14 Nov), tapi diaktifkan manual oleh admin
        $event = Event::factory()->create([
            'event_date' => '2026-11-14',
            'status' => EventStatus::Active,
            'is_manual_status' => true,
        ]);

        // Sistem otomatis tidak boleh menutup acara yang dikontrol manual oleh admin!
        $this->assertFalse($event->syncStatus());
        $this->assertSame(EventStatus::Active, $event->fresh()->status);
        $this->assertTrue($event->isOpen());

        Carbon::setTestNow();
    }

    public function test_admin_visiting_events_index_syncs_outdated_automatic_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 10:00:00', 'Asia/Jakarta'));

        $admin = User::factory()->admin()->create();

        // Acara tanggal 14 yang sebelumnya draft
        $event = Event::factory()->auto()->create([
            'event_date' => '2026-11-14',
            'status' => EventStatus::Draft,
        ]);

        $this->actingAs($admin)->get(route('events.index'))->assertOk();

        // Status di database otomatis menjadi Closed
        $this->assertSame(EventStatus::Closed, $event->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_status_update_broadcasts_event_status_updated_realtime(): void
    {
        EventFacade::fake([EventStatusUpdated::class]);

        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->put(route('events.update', $event), [
            'package_id' => $event->package_id,
            'theme_id' => $event->theme_id,
            'category' => $event->category->value,
            'party_one_name' => $event->party_one_name,
            'party_two_name' => $event->party_two_name,
            'event_date' => '2026-11-20',
            'gallery_visibility' => 'public',
            'status' => 'closed',
            'is_manual_status' => true,
        ])->assertSessionHasNoErrors();

        EventFacade::assertDispatched(EventStatusUpdated::class, function ($e) use ($event) {
            $channels = array_map(fn ($c) => $c->name, $e->broadcastOn());

            return in_array('events.'.$event->slug, $channels, true)
                && in_array('admin.events', $channels, true)
                && $e->broadcastWith()['status'] === 'closed'
                && $e->broadcastWith()['is_manual_status'] === true;
        });
    }
}
