<?php

namespace Database\Seeders;

use App\Models\Frame;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FrameSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('frames/defaults');

        $specs = [
            1 => ['name' => 'Classic Minimalist (1 Foto)', 'width' => 1080, 'height' => 1440, 'filename' => 'layout-1-classic.png'],
            2 => ['name' => 'Vintage Strip (2 Pose)', 'width' => 900, 'height' => 1800, 'filename' => 'layout-2-strip.png'],
            3 => ['name' => 'Romantic Floral Strip (3 Pose)', 'width' => 900, 'height' => 2400, 'filename' => 'layout-3-floral.png'],
            4 => ['name' => 'Modern Grid (4 Pose)', 'width' => 1500, 'height' => 1900, 'filename' => 'layout-4-grid.png'],
        ];

        foreach ($specs as $layout => $spec) {
            $path = "frames/defaults/{$spec['filename']}";
            $fullPath = Storage::disk('public')->path($path);

            if (! file_exists($fullPath)) {
                $img = imagecreatetruecolor($spec['width'], $spec['height']);
                imagesavealpha($img, true);
                $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
                if ($transparent !== false) {
                    imagefill($img, 0, 0, $transparent);
                }

                // Draw decorative subtle border overlay
                $borderColor = imagecolorallocatealpha($img, 220, 214, 204, 30);
                if ($borderColor !== false) {
                    imagesetthickness($img, 6);
                    imagerectangle($img, 24, 24, $spec['width'] - 24, $spec['height'] - 24, $borderColor);
                }

                imagepng($img, $fullPath);
                imagedestroy($img);
            }

            Frame::updateOrCreate(
                ['path' => $path],
                [
                    'event_id' => null,
                    'name' => $spec['name'],
                    'layout' => $layout,
                    'is_active' => true,
                ]
            );
        }
    }
}
