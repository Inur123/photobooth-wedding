<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('events:sync-statuses')
    ->dailyAt('00:01')
    ->timezone('Asia/Jakarta')
    ->description('Otomatis tutup kiriman tamu setelah hari-H dan tandai kadaluarsa setelah masa paket galeri habis');
