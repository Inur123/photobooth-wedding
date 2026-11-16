<?php

namespace App\Models;

use App\Enums\ContributionType;
use Database\Factories\ContributionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string $event_id
 * @property string|null $frame_id
 * @property ContributionType $type
 * @property int|null $layout
 * @property string|null $guest_name
 * @property string|null $message
 * @property string|null $file_path
 * @property string|null $thumbnail_path
 * @property string|null $mime_type
 * @property int $size_bytes
 * @property int|null $duration_seconds
 * @property bool $is_approved
 * @property string $visibility
 * @property string $file_disk
 * @property int|null $width
 * @property int|null $height
 * @property string|null $dominant_color
 * @property string $processing_status
 * @property string|null $client_upload_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event|null $event
 * @property-read Frame|null $frame
 */
#[Fillable([
    'event_id', 'frame_id', 'type', 'layout', 'guest_name', 'message', 'file_path',
    'thumbnail_path', 'mime_type', 'size_bytes', 'duration_seconds', 'is_approved',
    'visibility', 'file_disk', 'width', 'height', 'dominant_color', 'processing_status', 'client_upload_id',
])]
class Contribution extends Model
{
    /** @use HasFactory<ContributionFactory> */
    use HasFactory, HasUuids;

    protected $appends = ['file_url', 'thumbnail_url'];

    protected $hidden = ['file_path', 'thumbnail_path', 'file_disk'];

    protected $attributes = [
        'is_approved' => true,
    ];

    protected function casts(): array
    {
        return [
            'type' => ContributionType::class,
            'is_approved' => 'boolean',
        ];
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Frame, $this> */
    public function frame(): BelongsTo
    {
        return $this->belongsTo(Frame::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if ($this->file_disk === 'r2') {
            return Storage::disk('r2')->url($this->file_path);
        }

        return route('contributions.media', $this);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail_path) {
            return null;
        }

        if ($this->file_disk === 'r2') {
            return Storage::disk('r2')->url($this->thumbnail_path);
        }

        return route('contributions.media', ['contribution' => $this, 'asset' => 'thumbnail']);
    }
}
