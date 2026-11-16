<?php

namespace App\Models;

use Database\Factories\FrameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $event_id
 * @property string $name
 * @property int $layout
 * @property string $path
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['event_id', 'name', 'layout', 'path', 'is_active'])]
class Frame extends Model
{
    /** @use HasFactory<FrameFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * A null event_id means a global frame available to every event.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
