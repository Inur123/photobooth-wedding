<?php

use App\Http\Controllers\ClientEventController;
use App\Http\Controllers\ClientPackageController;
use App\Http\Controllers\ContributionMediaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventExperienceController;
use App\Http\Controllers\SitemapController;
use App\Models\Package;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Route::has('register'),
        'packages' => Package::where('is_active', true)->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'slug',
                'tagline',
                'price',
                'benefits',
                'gallery_days',
                'max_frames',
                'allow_zip_download',
                'allow_custom_theme',
                'allow_video',
                'max_video_seconds',
            ]),
    ]);
})->name('home');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('terms', fn () => Inertia::render('TermsOfService'))->name('terms');
Route::get('privacy', fn () => Inertia::render('PrivacyPolicy'))->name('privacy');

Route::prefix('e/{event:slug}')->name('event.')->group(function () {
    Route::get('owner/{token}/{section?}', [EventExperienceController::class, 'showOwner'])
        ->whereIn('section', ['album', 'reels', 'catatan', 'acara', 'titip'])
        ->middleware('throttle:30,1')
        ->name('owner');
    Route::get('/', [EventExperienceController::class, 'show'])->name('show');
    Route::post('contributions', [EventExperienceController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('contributions.store');
    Route::get('gallery', [EventExperienceController::class, 'gallery'])->name('gallery');
    Route::get('{section}', [EventExperienceController::class, 'show'])
        ->whereIn('section', ['album', 'reels', 'catatan', 'acara', 'titip'])
        ->name('section');
});

Route::get('media/contributions/{contribution}/{asset?}', ContributionMediaController::class)
    ->whereIn('asset', ['file', 'thumbnail'])->name('contributions.media');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('my-packages', [ClientPackageController::class, 'index'])->name('client.packages.index');

    Route::prefix('my-events')->name('client.events.')->group(function () {
        Route::get('/', [ClientEventController::class, 'index'])->name('index');

        Route::prefix('{event}')->group(function () {
            Route::get('/', [ClientEventController::class, 'show'])->name('show');
            Route::put('/', [ClientEventController::class, 'update'])->name('update');
            Route::patch('contributions/{contribution}/toggle', [ClientEventController::class, 'toggleContribution'])->name('contributions.toggle');
            Route::delete('contributions/{contribution}', [ClientEventController::class, 'destroyContribution'])->name('contributions.destroy');
            Route::get('download-zip', [ClientEventController::class, 'downloadZip'])->name('download-zip');
        });
    });
});

require __DIR__.'/settings.php';
