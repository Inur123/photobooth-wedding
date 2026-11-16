<?php

namespace Database\Factories;

use App\Models\Frame;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Frame>
 */
class FrameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => null,
            'name' => fake()->words(2, true),
            'layout' => fake()->numberBetween(1, 4),
            'path' => 'frames/'.fake()->uuid().'.png',
            'is_active' => true,
        ];
    }
}
