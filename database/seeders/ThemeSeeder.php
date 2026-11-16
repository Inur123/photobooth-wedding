<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            ['Titip Momen', 'titip-momen', 'Bold · Default', '#1d1c29', '#ff5d6c', '#f8f6f2'],
            ['Cream & Ink', 'cream-ink', 'Clean · Minimal', '#2b2a33', '#b8956a', '#f6f1e9'],
            ['Peach Fizz', 'peach-fizz', 'Hangat · Ceria', '#7a3b2e', '#ff9d7a', '#fff1e6'],
            ['Matcha', 'matcha', 'Segar · Garden', '#2f4a3a', '#9bb86a', '#f1f4e6'],
            ['Lilac Hour', 'lilac-hour', 'Lembut · Modern', '#40356b', '#b79cf2', '#f3eefc'],
            ['Tinta Malam', 'tinta-malam', 'Gelap · Formal', '#f3ecdd', '#e8b04a', '#14131f'],
        ];

        Theme::query()->whereNotIn('slug', array_column($themes, 1))->delete();

        foreach ($themes as $i => [$name, $slug, $mood, $primary, $accent, $neutral]) {
            Theme::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'mood' => $mood,
                'primary_color' => $primary,
                'accent_color' => $accent,
                'neutral_color' => $neutral,
                'sort_order' => $i,
            ]);
        }
    }
}
