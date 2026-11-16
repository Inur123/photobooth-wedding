<?php

use App\Http\Controllers\Admin\AdminContributionController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminPackageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->group(function () {
        Route::redirect('/admin', '/dashboard');

        // Events
        Route::patch('events/{event}/status', [AdminEventController::class, 'toggleStatus'])->name('events.status');
        Route::resource('events', AdminEventController::class)->except(['show']);

        // Packages
        Route::resource('packages', AdminPackageController::class)->only(['index', 'edit', 'update']);

        // Contributions
        Route::get('contributions', [AdminContributionController::class, 'index'])->name('contributions.index');
        Route::patch('contributions/{contribution}/approve', [AdminContributionController::class, 'approve'])->name('contributions.approve');
        Route::patch('contributions/{contribution}/reject', [AdminContributionController::class, 'reject'])->name('contributions.reject');
        Route::delete('contributions/{contribution}', [AdminContributionController::class, 'destroy'])->name('contributions.destroy');
    });
