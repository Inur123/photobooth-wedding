<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Journal '.fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'tagline' => fake()->sentence(),
            'price' => fake()->randomElement([200000, 275000, 350000, 475000]),
            'benefits' => ['Galeri real-time', 'Akses galeri 30 hari'],
            'gallery_days' => 30,
            'max_frames' => 4,
            'allow_zip_download' => false,
            'allow_custom_theme' => false,
            'allow_video' => true,
            'max_video_seconds' => 45,
            'max_video_kb' => 25600,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
