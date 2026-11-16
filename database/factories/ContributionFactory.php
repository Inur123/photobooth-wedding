<?php

namespace Database\Factories;

use App\Enums\ContributionType;
use App\Models\Contribution;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contribution>
 */
class ContributionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'type' => ContributionType::Photo,
            'layout' => 1,
            'guest_name' => fake()->firstName(),
            'file_path' => 'contributions/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => fake()->numberBetween(80_000, 900_000),
            'is_approved' => true,
        ];
    }

    public function wish(): static
    {
        return $this->state([
            'type' => ContributionType::Wish,
            'layout' => null,
            'message' => fake()->sentence(),
            'file_path' => null,
            'mime_type' => null,
            'size_bytes' => 0,
        ]);
    }
}
