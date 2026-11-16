<?php

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Contribution;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class SyncEventStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:sync-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui status acara secara otomatis: tutup kiriman setelah hari-H dan tandai kadaluarsa setelah masa paket galeri habis.';

    public function handle(): int
    {
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $now = Carbon::now('Asia/Jakarta');

        // 1. Kadaluarsa: Event yang masa aktif galeri paketnya telah lewat (now > expires_at)
        $expiredCount = Event::where('status', '!=', EventStatus::Expired)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->update(['status' => EventStatus::Expired]);

        // 2. Selesai (Kiriman Ditutup): Event aktif non-manual yang hari-H telah lewat (event_date < today) dan belum kadaluarsa
        $closedCount = Event::where('is_manual_status', false)
            ->whereIn('status', [EventStatus::Active, EventStatus::Draft])
            ->whereNotNull('event_date')
            ->whereDate('event_date', '<', $today)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->update(['status' => EventStatus::Closed]);

        // 3. Buka Otomatis: Event draft non-manual yang hari-H tiba hari ini
        Event::where('is_manual_status', false)
            ->where('status', EventStatus::Draft)
            ->whereNotNull('event_date')
            ->whereDate('event_date', $today)
            ->update(['status' => EventStatus::Active]);

        // 4. Auto-Purge Media (H+7 setelah kadaluarsa): hapus file fisik kiriman non-demo agar storage tetap terkendali
        $purgeCutoff = $now->copy()->subDays(7);
        $purgedEventsCount = 0;

        Event::query()
            ->where('status', EventStatus::Expired)
            ->where('is_demo', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $purgeCutoff)
            ->whereHas('contributions', function ($query) {
                $query->whereNotNull('file_path')->orWhereNotNull('thumbnail_path');
            })
            ->chunkById(50, function ($events) use (&$purgedEventsCount) {
                foreach ($events as $event) {
                    $event->contributions()
                        ->where(function ($query) {
                            $query->whereNotNull('file_path')->orWhereNotNull('thumbnail_path');
                        })
                        ->chunkById(100, function ($contributions) {
                            foreach ($contributions as $contribution) {
                                /** @var Contribution $contribution */
                                $disk = $contribution->file_disk ?: 'local';
                                if ($contribution->file_path && Storage::disk($disk)->exists($contribution->file_path)) {
                                    Storage::disk($disk)->delete($contribution->file_path);
                                }
                                if ($contribution->thumbnail_path && Storage::disk($disk)->exists($contribution->thumbnail_path)) {
                                    Storage::disk($disk)->delete($contribution->thumbnail_path);
                                }
                            }
                        });

                    Storage::disk('local')->deleteDirectory("events/{$event->id}");

                    $event->contributions()
                        ->where(function ($query) {
                            $query->whereNotNull('file_path')->orWhereNotNull('thumbnail_path');
                        })
                        ->update([
                            'file_path' => null,
                            'thumbnail_path' => null,
                            'size_bytes' => 0,
                        ]);

                    $purgedEventsCount++;
                }
            });

        $this->info("Sinkronisasi status acara selesai: {$closedCount} acara selesai (kiriman ditutup), {$expiredCount} acara kadaluarsa, {$purgedEventsCount} media acara kadaluarsa dibersihkan.");

        return self::SUCCESS;
    }
}
