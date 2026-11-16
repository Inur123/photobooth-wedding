<?php

namespace Database\Factories;

use App\Enums\EventCategory;
use App\Enums\EventStatus;
use App\Enums\GalleryVisibility;
use App\Models\Event;
use App\Models\Package;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $one = fake()->firstName();
        $two = fake()->firstName();

        return [
            'package_id' => Package::factory(),
            'theme_id' => Theme::factory(),
            'slug' => Str::slug("$one-$two").'-'.Str::lower(Str::random(5)),
            'category' => EventCategory::Wedding,
            'status' => EventStatus::Active,
            'is_manual_status' => true,
            'gallery_visibility' => GalleryVisibility::Public,
            'party_one_name' => $one,
            'party_two_name' => $two,
            'event_date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'venue' => fake()->address(),
            'is_demo' => false,
            'expires_at' => null,
        ];
    }

    public function auto(): static
    {
        return $this->state(['is_manual_status' => false]);
    }

    public function draft(): static
    {
        return $this->state(['status' => EventStatus::Draft]);
    }

    public function closed(): static
    {
        return $this->state(['status' => EventStatus::Closed]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => EventStatus::Expired,
            'expires_at' => now()->subDay(),
        ]);
    }
}
