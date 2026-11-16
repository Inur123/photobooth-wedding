<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPackageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/packages/Index', [
            'packages' => Package::withCount('events')->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(Package $package): Response
    {
        return Inertia::render('admin/packages/Edit', [
            'package' => $package,
            'coreBenefits' => Package::coreBenefits(),
        ]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'price' => ['required', 'integer', 'min:0'],
            'allow_zip_download' => ['boolean'],
            'allow_custom_theme' => ['boolean'],
            'allow_video' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($data['allow_video'])) {
            $data['max_video_seconds'] = $package->max_video_seconds > 0 ? $package->max_video_seconds : 20;
            $data['max_video_kb'] = $package->max_video_kb > 0 ? $package->max_video_kb : 15360;
        }

        $package->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Paket berhasil diperbarui.']);

        return back();
    }
}
