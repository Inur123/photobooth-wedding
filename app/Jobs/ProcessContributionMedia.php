<?php

namespace App\Jobs;

use App\Events\ContributionUpdated;
use App\Models\Contribution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ProcessContributionMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public readonly Contribution $contribution) {}

    public function handle(): void
    {
        // Optimasi untuk environment VPS: alokasi memori cukup untuk decode foto resolusi tinggi
        ini_set('memory_limit', '512M');

        $contribution = $this->contribution->fresh();
        if (! $contribution) {
            return;
        }

        // Hanya proses foto — video dan suara dipakai apa adanya (browser sudah kompres)
        if ($contribution->type->value !== 'photo' || ! $contribution->file_path) {
            $contribution->update(['processing_status' => 'done']);

            return;
        }

        try {
            $diskName = $contribution->file_disk ?: 'r2';
            $disk = Storage::disk($diskName);

            if (! $disk->exists($contribution->file_path)) {
                $contribution->update(['processing_status' => 'failed']);

                return;
            }

            $rawContents = $disk->get($contribution->file_path);
            if ($rawContents === null) {
                $contribution->update(['processing_status' => 'failed']);

                return;
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'r2_');
            if ($tmpFile === false) {
                $contribution->update(['processing_status' => 'failed']);

                return;
            }

            try {
                file_put_contents($tmpFile, $rawContents);

                $manager = new ImageManager(new Driver);
                $image = $manager->decodePath($tmpFile);

                // Auto-orient berdasarkan EXIF kamera HP sebelum strip EXIF & convert ke WebP
                $image->orient();

                // Resize ke maksimal 2048px pada sisi terpanjang, tanpa upscale
                $image->scaleDown(2048, 2048);

                $w = $image->width();
                $h = $image->height();

                // Ambil warna dominan menggunakan GD langsung (lebih efisien)
                $dominantColor = $this->sampleDominantColorGd($tmpFile);

                // Encode ke WebP 85%
                $encoded = (string) $image->encodeUsingFormat(Format::WEBP, quality: 85);

                // Simpan sebagai file WebP (gunakan path yang sama jika sudah .webp agar URL tidak berubah)
                $eventId = $contribution->event_id;
                $uuid = $contribution->id;
                $prefix = $diskName === 'r2' ? 'photobooth/' : '';
                $newPath = str_ends_with(strtolower($contribution->file_path), '.webp')
                    ? $contribution->file_path
                    : "{$prefix}events/{$eventId}/photo/{$uuid}.webp";
                $disk->put($newPath, $encoded);
            } finally {
                @unlink($tmpFile);
            }

            // Hapus file lama jika path berbeda (misal konversi dari .jpg/.png ke .webp)
            if ($contribution->file_path !== $newPath) {
                $disk->delete($contribution->file_path);
            }

            $contribution->update([
                'file_path' => $newPath,
                'mime_type' => 'image/webp',
                'size_bytes' => strlen($encoded),
                'width' => $w,
                'height' => $h,
                'dominant_color' => $dominantColor,
                'processing_status' => 'done',
            ]);

            // Siarkan pembaruan foto terkompresi secara real-time via Reverb
            event(new ContributionUpdated($contribution->fresh('event')));
        } catch (\Throwable) {
            $contribution->update(['processing_status' => 'failed']);
        }
    }

    /**
     * Ambil warna dominan dengan rata-rata sampel piksel dari tengah gambar menggunakan GD.
     */
    private function sampleDominantColorGd(string $path): string
    {
        $gd = @imagecreatefromstring((string) file_get_contents($path));
        if ($gd === false) {
            return '#cccccc';
        }

        $w = imagesx($gd);
        $h = imagesy($gd);
        $totalR = 0;
        $totalG = 0;
        $totalB = 0;
        $count = 0;

        // Sampel 5×5 area di tengah gambar (25-75% dari width/height)
        $stepX = max(1, (int) ($w * 0.5 / 5));
        $stepY = max(1, (int) ($h * 0.5 / 5));

        for ($x = (int) ($w * 0.25); $x <= (int) ($w * 0.75); $x += $stepX) {
            for ($y = (int) ($h * 0.25); $y <= (int) ($h * 0.75); $y += $stepY) {
                $rgb = imagecolorat($gd, $x, $y);
                if ($rgb === false) {
                    continue;
                }
                $totalR += ($rgb >> 16) & 0xFF;
                $totalG += ($rgb >> 8) & 0xFF;
                $totalB += $rgb & 0xFF;
                $count++;
            }
        }

        imagedestroy($gd);

        if ($count === 0) {
            return '#cccccc';
        }

        return sprintf('#%02x%02x%02x',
            (int) ($totalR / $count),
            (int) ($totalG / $count),
            (int) ($totalB / $count),
        );
    }

    public function failed(\Throwable $exception): void
    {
        $this->contribution->update(['processing_status' => 'failed']);
    }
}
