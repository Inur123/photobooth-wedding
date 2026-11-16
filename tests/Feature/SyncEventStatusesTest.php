<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Contribution;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SyncEventStatusesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_command_closes_events_past_event_date_and_expires_past_expiry_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 12:00:00', 'Asia/Jakarta'));

        // 1. Hari-H kemarin, masa galeri masih ada sampai Desember -> Harus otomatis CLOSED (Selesai)
        $pastEvent = Event::factory()->auto()->create([
            'status' => EventStatus::Active,
            'event_date' => '2026-11-08',
            'expires_at' => Carbon::parse('2026-12-08 23:59:59', 'Asia/Jakarta'),
        ]);

        // 2. Masa aktif galeri sudah habis kemarin -> Harus otomatis EXPIRED (Kadaluarsa)
        $expiredEvent = Event::factory()->create([
            'status' => EventStatus::Closed,
            'event_date' => '2026-10-01',
            'expires_at' => Carbon::parse('2026-11-08 23:59:59', 'Asia/Jakarta'),
        ]);

        // 3. Hari-H hari ini (sedang berlangsung) -> Harus tetap ACTIVE
        $todayEvent = Event::factory()->create([
            'status' => EventStatus::Active,
            'event_date' => '2026-11-09',
            'expires_at' => Carbon::parse('2026-12-09 23:59:59', 'Asia/Jakarta'),
        ]);

        // 4. Hari-H di masa depan -> Harus tetap ACTIVE
        $futureEvent = Event::factory()->create([
            'status' => EventStatus::Active,
            'event_date' => '2026-11-20',
            'expires_at' => Carbon::parse('2026-12-20 23:59:59', 'Asia/Jakarta'),
        ]);

        // 5. Draft -> Tetap DRAFT
        $draftEvent = Event::factory()->draft()->create([
            'event_date' => '2026-11-20',
        ]);

        $this->artisan('events:sync-statuses')
            ->expectsOutputToContain('1 acara selesai (kiriman ditutup), 1 acara kadaluarsa, 0 media acara kadaluarsa dibersihkan.')
            ->assertSuccessful();

        $this->assertSame(EventStatus::Closed, $pastEvent->fresh()->status);
        $this->assertSame(EventStatus::Expired, $expiredEvent->fresh()->status);
        $this->assertSame(EventStatus::Active, $todayEvent->fresh()->status);
        $this->assertSame(EventStatus::Active, $futureEvent->fresh()->status);
        $this->assertSame(EventStatus::Draft, $draftEvent->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_sync_command_purges_media_files_for_non_demo_events_more_than_7_days_expired(): void
    {
        Storage::fake('local');
        Carbon::setTestNow(Carbon::parse('2026-11-20 12:00:00', 'Asia/Jakarta'));

        // Expired 8 hari lalu (harus dibersihkan medianya)
        $purgeEvent = Event::factory()->create([
            'status' => EventStatus::Expired,
            'is_demo' => false,
            'event_date' => '2026-10-01',
            'expires_at' => Carbon::parse('2026-11-12 23:59:59', 'Asia/Jakarta'),
        ]);
        $purgePath = "events/{$purgeEvent->id}/contributions/old.jpg";
        Storage::disk('local')->put($purgePath, 'old-photo');
        $purgedContribution = Contribution::factory()->for($purgeEvent)->create([
            'type' => 'photo',
            'file_disk' => 'local',
            'file_path' => $purgePath,
            'size_bytes' => 5000,
        ]);

        // Expired 5 hari lalu (masih dalam masa tenggang H+7, tidak boleh terhapus)
        $graceEvent = Event::factory()->create([
            'status' => EventStatus::Expired,
            'is_demo' => false,
            'event_date' => '2026-10-05',
            'expires_at' => Carbon::parse('2026-11-15 23:59:59', 'Asia/Jakarta'),
        ]);
        $gracePath = "events/{$graceEvent->id}/contributions/grace.jpg";
        Storage::disk('local')->put($gracePath, 'grace-photo');
        $graceContribution = Contribution::factory()->for($graceEvent)->create([
            'type' => 'photo',
            'file_disk' => 'local',
            'file_path' => $gracePath,
            'size_bytes' => 5000,
        ]);

        // Demo event expired 10 hari lalu (harus selalu dilindungi)
        $demoEvent = Event::factory()->create([
            'status' => EventStatus::Expired,
            'is_demo' => true,
            'event_date' => '2026-10-01',
            'expires_at' => Carbon::parse('2026-11-10 23:59:59', 'Asia/Jakarta'),
        ]);
        $demoPath = "events/{$demoEvent->id}/contributions/demo.jpg";
        Storage::disk('local')->put($demoPath, 'demo-photo');
        $demoContribution = Contribution::factory()->for($demoEvent)->create([
            'type' => 'photo',
            'file_disk' => 'local',
            'file_path' => $demoPath,
            'size_bytes' => 5000,
        ]);

        $this->artisan('events:sync-statuses')
            ->expectsOutputToContain('1 media acara kadaluarsa dibersihkan.')
            ->assertSuccessful();

        Storage::disk('local')->assertMissing($purgePath);
        Storage::disk('local')->assertExists($gracePath);
        Storage::disk('local')->assertExists($demoPath);

        $purgedContribution->refresh();
        $this->assertNull($purgedContribution->file_path);
        $this->assertNull($purgedContribution->thumbnail_path);
        $this->assertSame(0, $purgedContribution->size_bytes);

        $this->assertSame($gracePath, $graceContribution->fresh()->file_path);
        $this->assertSame($demoPath, $demoContribution->fresh()->file_path);

        Carbon::setTestNow();
    }
}
