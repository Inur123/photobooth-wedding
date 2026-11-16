<?php

namespace App\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $tagline
 * @property int $price
 * @property array<int, string>|null $benefits
 * @property int $gallery_days
 * @property int $max_frames
 * @property bool $allow_zip_download
 * @property bool $allow_custom_theme
 * @property bool $allow_video
 * @property int $max_video_seconds
 * @property int $max_video_kb
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'slug',
    'tagline',
    'price',
    'benefits',
    'gallery_days',
    'max_frames',
    'allow_zip_download',
    'allow_custom_theme',
    'allow_video',
    'max_video_seconds',
    'max_video_kb',
    'is_active',
    'sort_order',
])]
class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'price' => 'integer',
            'gallery_days' => 'integer',
            'max_frames' => 'integer',
            'allow_zip_download' => 'boolean',
            'allow_custom_theme' => 'boolean',
            'allow_video' => 'boolean',
            'max_video_seconds' => 'integer',
            'max_video_kb' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Package $package): void {
            $package->benefits = $package->resolvedBenefits();
        });
    }

    /**
     * Core capabilities included in every package.
     *
     * @return list<string>
     */
    public static function coreBenefits(): array
    {
        return [
            'Halaman photobooth dengan nama & tanggal acara',
            'QR code & tautan acara',
            'Galeri real-time',
        ];
    }

    /**
     * Derive the facility checklist directly from enforced package capabilities.
     *
     * @return list<string>
     */
    public static function buildBenefits(
        int $galleryDays,
        int $maxFrames,
        bool $allowZipDownload,
        bool $allowCustomTheme,
        bool $allowVideo = false,
        int $maxVideoSeconds = 0,
    ): array {
        $items = self::coreBenefits();

        $clampedLayouts = min(4, max(1, $maxFrames));
        $items[] = $clampedLayouts === 1
            ? '1 pilihan layout foto (1 pose berbingkai)'
            : "{$clampedLayouts} pilihan layout foto (1–{$clampedLayouts} pose)";

        $items[] = $allowVideo
            ? ($maxVideoSeconds > 0
                ? "Kiriman video (maks {$maxVideoSeconds} dtk), voice note & ucapan"
                : 'Kiriman video, voice note & ucapan')
            : 'Kiriman voice note & ucapan tertulis';

        if ($allowCustomTheme) {
            $items[] = 'Tema warna custom (halaman & bingkai)';
        }

        if ($allowZipDownload) {
            $items[] = 'Unduh semua file (ZIP)';
        }

        $items[] = "Galeri aktif {$galleryDays} hari";

        return $items;
    }

    /** @return list<int> */
    public function allowedPhotoLayouts(): array
    {
        return range(1, min(4, max(1, (int) $this->max_frames)));
    }

    /** @return list<string> */
    public function allowedContributionTypes(): array
    {
        $types = ['photo'];
        if ($this->allow_video) {
            $types[] = 'video';
        }
        $types[] = 'voice';
        $types[] = 'wish';

        return $types;
    }

    public function resolvedVideoMaxSeconds(): int
    {
        if (! $this->allow_video) {
            return 0;
        }

        return max(5, (int) ($this->max_video_seconds ?: 20));
    }

    public function resolvedVideoMaxKb(): int
    {
        if (! $this->allow_video) {
            return 0;
        }

        return max(1024, (int) ($this->max_video_kb ?: 15360));
    }

    /** @return list<string> */
    public function resolvedBenefits(): array
    {
        return self::buildBenefits(
            (int) ($this->gallery_days ?: 30),
            (int) ($this->max_frames ?: 1),
            (bool) $this->allow_zip_download,
            (bool) $this->allow_custom_theme,
            (bool) $this->allow_video,
            $this->resolvedVideoMaxSeconds(),
        );
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
