<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    public function definition(): array
    {
        /** @var array<int, string> $words */
        $words = fake()->unique()->words(2);
        $name = implode(' ', $words);

        return [
            'name' => ucwords($name),
            'slug' => str($name)->slug()->toString(),
            'mood' => fake()->word(),
            'primary_color' => '#1d1c29',
            'accent_color' => '#ff5d6c',
            'neutral_color' => '#f8f6f2',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
