<?php

namespace App\Models;

use App\Enums\EventCategory;
use App\Enums\EventStatus;
use App\Enums\GalleryVisibility;
use App\Events\EventStatusUpdated;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use function is_array;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string|null $package_id
 * @property string|null $theme_id
 * @property string $slug
 * @property EventCategory $category
 * @property EventStatus $status
 * @property bool $is_manual_status
 * @property GalleryVisibility $gallery_visibility
 * @property string $party_one_name
 * @property string|null $party_two_name
 * @property Carbon|null $event_date
 * @property string|null $venue
 * @property array{primary: string, accent: string, neutral: string}|null $custom_colors
 * @property string|null $cover_path
 * @property string|null $owner_access_token
 * @property string|null $notes
 * @property bool $is_demo
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Package|null $package
 * @property-read Theme|null $theme
 */
#[Fillable([
    'user_id', 'package_id', 'theme_id', 'slug', 'category', 'status', 'is_manual_status', 'gallery_visibility',
    'party_one_name', 'party_two_name', 'event_date', 'venue', 'custom_colors',
    'cover_path', 'notes', 'is_demo', 'expires_at',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasUuids;

    protected $hidden = ['owner_access_token'];

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            $event->owner_access_token ??= Str::random(64);
        });

        static::saving(function (Event $event) {
            $package = $event->package_id
                ? ($event->relationLoaded('package') ? $event->package : Package::find($event->package_id))
                : null;

            if ($package && ! $package->allow_custom_theme) {
                $event->theme_id = null;
                $event->custom_colors = null;
            }

            if ($event->isDirty('expires_at') && $event->expires_at !== null) {
                return;
            }

            if ($event->event_date && $package && $package->gallery_days > 0) {
                $event->expires_at = $event->event_date->copy()->timezone('Asia/Jakarta')->addDays($package->gallery_days)->endOfDay();
            }

            // Jika status tidak diatur manual oleh admin dan status tidak sedang diisi secara eksplisit, sinkronkan otomatis
            if (! $event->is_manual_status && ! $event->isDirty('status')) {
                $event->status = $event->resolveAutomaticStatus();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'category' => EventCategory::class,
            'status' => EventStatus::class,
            'is_manual_status' => 'boolean',
            'gallery_visibility' => GalleryVisibility::class,
            'event_date' => 'date',
            'custom_colors' => 'array',
            'is_demo' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** @return BelongsTo<Theme, $this> */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /** @return HasMany<Contribution, $this> */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /** @return HasMany<Frame, $this> */
    public function frames(): HasMany
    {
        return $this->hasMany(Frame::class);
    }

    /** @return Collection<int, Frame> */
    public function availableFrames(): Collection
    {
        return Frame::query()
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('event_id')->orWhere('event_id', $this->id))
            ->orderByRaw('CASE WHEN event_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('id')
            ->limit(max(0, $this->package->max_frames ?? 1))
            ->get();
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Active)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isOpen(): bool
    {
        return $this->status === EventStatus::Active
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function supportsCustomTheme(): bool
    {
        return (bool) $this->package?->allow_custom_theme;
    }

    /**
     * Resolved theme colors: custom colors win, then theme, then default.
     *
     * @return array{primary: string, accent: string, neutral: string}
     */
    public function colors(): array
    {
        if (! $this->supportsCustomTheme()) {
            return ['primary' => '#1d1c29', 'accent' => '#ff5d6c', 'neutral' => '#f8f6f2'];
        }

        if (is_array($this->custom_colors)
            && isset($this->custom_colors['primary'], $this->custom_colors['accent'], $this->custom_colors['neutral'])
        ) {
            return [
                'primary' => (string) $this->custom_colors['primary'],
                'accent' => (string) $this->custom_colors['accent'],
                'neutral' => (string) $this->custom_colors['neutral'],
            ];
        }

        if ($this->theme) {
            return [
                'primary' => $this->theme->primary_color,
                'accent' => $this->theme->accent_color,
                'neutral' => $this->theme->neutral_color,
            ];
        }

        return ['primary' => '#1d1c29', 'accent' => '#ff5d6c', 'neutral' => '#f8f6f2'];
    }

    /**
     * Hitung status otomatis berdasarkan tanggal acara dan masa aktif galeri.
     */
    public function resolveAutomaticStatus(?Carbon $now = null): EventStatus
    {
        $now = $now ?? Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // 1. Jika masa aktif galeri paket telah lewat, status adalah Expired
        if ($this->expires_at !== null && $this->expires_at->lt($now)) {
            return EventStatus::Expired;
        }

        // 2. Jika tanggal acara belum ditentukan, tetap Draft
        if (! $this->event_date) {
            return EventStatus::Draft;
        }

        $eventDateStr = $this->event_date instanceof Carbon
            ? $this->event_date->toDateString()
            : Carbon::parse($this->event_date)->toDateString();

        // 3. Jika hari-H sudah lewat (< today), status adalah Closed (kiriman ditutup otomatis)
        if ($eventDateStr < $today) {
            return EventStatus::Closed;
        }

        // 4. Jika hari-H sedang berlangsung (== today), status adalah Active (buka kiriman otomatis)
        if ($eventDateStr === $today) {
            return EventStatus::Active;
        }

        // 5. Jika hari-H belum tiba (> today), status adalah Draft (belum dibuka)
        return EventStatus::Draft;
    }

    /**
     * Sinkronkan status acara jika tidak dalam mode manual oleh admin.
     * Mengembalikan true jika status berubah.
     */
    public function syncStatus(bool $broadcast = true): bool
    {
        // Jika status telah diatur manual oleh admin, sistem otomatis tidak boleh mengubahnya!
        if ($this->is_manual_status) {
            return false;
        }

        $newStatus = $this->resolveAutomaticStatus();

        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);

            if ($broadcast) {
                event(new EventStatusUpdated($this->fresh(['package', 'theme'])));
            }

            return true;
        }

        return false;
    }

    /**
     * Sinkronkan semua acara yang berstatus otomatis.
     */
    public static function syncAllAutomaticStatuses(bool $broadcast = true): int
    {
        $updatedCount = 0;
        $events = static::query()
            ->where('is_manual_status', false)
            ->get();

        foreach ($events as $event) {
            if ($event->syncStatus($broadcast)) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
