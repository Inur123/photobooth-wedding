<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Launch packages — everything is digital, no print and no on-site crew.
     * Names, prices and limits are placeholders; adjust them from the admin later.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'tagline' => 'Semua fitur inti, siap dipakai di hari-H.',
                'price' => 150000,
                'gallery_days' => 30,
                'max_frames' => 1,
                'allow_zip_download' => false,
                'allow_custom_theme' => false,
                'allow_video' => false,
                'max_video_seconds' => 0,
                'max_video_kb' => 0,
            ],
            [
                'name' => 'Plus',
                'slug' => 'plus',
                'tagline' => 'Tampilan menyesuaikan warna acaramu.',
                'price' => 250000,
                'gallery_days' => 60,
                'max_frames' => 2,
                'allow_zip_download' => true,
                'allow_custom_theme' => true,
                'allow_video' => true,
                'max_video_seconds' => 20,
                'max_video_kb' => 15360,
            ],
            [
                'name' => 'Full',
                'slug' => 'full',
                'tagline' => 'Paling lengkap, untuk acara yang ingin serba personal.',
                'price' => 350000,
                'gallery_days' => 90,
                'max_frames' => 4,
                'allow_zip_download' => true,
                'allow_custom_theme' => true,
                'allow_video' => true,
                'max_video_seconds' => 45,
                'max_video_kb' => 25600,
            ],
        ];

        Package::query()->whereNotIn('slug', array_column($packages, 'slug'))->delete();

        foreach ($packages as $i => $package) {
            Package::updateOrCreate(['slug' => $package['slug']], $package + ['sort_order' => $i]);
        }
    }
}
