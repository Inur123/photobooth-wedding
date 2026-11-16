<?php

namespace App\Models;

use Database\Factories\ThemeFactory;
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
 * @property string|null $mood
 * @property string $primary_color
 * @property string $accent_color
 * @property string $neutral_color
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'mood', 'primary_color', 'accent_color', 'neutral_color', 'is_active', 'sort_order'])]
class Theme extends Model
{
    /** @use HasFactory<ThemeFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
