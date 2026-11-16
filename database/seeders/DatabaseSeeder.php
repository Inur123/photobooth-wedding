<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Package;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([PackageSeeder::class, ThemeSeeder::class, FrameSeeder::class]);

        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        Event::create([
            'package_id' => Package::where('slug', 'plus')->value('id'),
            'theme_id' => Theme::where('slug', 'titip-momen')->value('id'),
            'slug' => 'demo-wedding',
            'status' => EventStatus::Active,
            'is_manual_status' => true,
            'is_demo' => true,
            'party_one_name' => 'Anisa',
            'party_two_name' => 'Rizky',
            'event_date' => now()->addMonth(),
            'venue' => 'Magetan, Jawa Timur',
        ]);
    }
}
