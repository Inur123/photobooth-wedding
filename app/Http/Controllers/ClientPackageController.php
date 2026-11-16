<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientPackageController extends Controller
{
    public function index(Request $request): Response
    {
        $userEvents = Event::where('user_id', $request->user()->id)
            ->with(['package'])
            ->latest()
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'slug' => $event->slug,
                'party_one_name' => $event->party_one_name,
                'party_two_name' => $event->party_two_name,
                'display_name' => $event->party_one_name.($event->party_two_name ? ' & '.$event->party_two_name : ''),
                'event_date' => $event->event_date?->toDateString(),
                'expires_at' => $event->expires_at?->toIso8601String(),
                'status' => $event->status->value,
                'package' => [
                    'id' => $event->package->id,
                    'name' => $event->package->name,
                    'slug' => $event->package->slug,
                    'price' => $event->package->price,
                    'tagline' => $event->package->tagline,
                    'gallery_days' => $event->package->gallery_days,
                    'max_frames' => $event->package->max_frames,
                    'allow_zip_download' => $event->package->allow_zip_download,
                    'allow_custom_theme' => $event->package->allow_custom_theme,
                    'allow_video' => $event->package->allow_video,
                    'max_video_seconds' => $event->package->resolvedVideoMaxSeconds(),
                    'benefits' => $event->package->benefits ?? [],
                ],
            ]);

        $activeUsage = $userEvents->groupBy(fn ($e) => $e['package']['id'])
            ->map(fn ($group) => $group->pluck('display_name')->implode(', '));

        $availablePackages = Package::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Package $pkg) => [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'slug' => $pkg->slug,
                'tagline' => $pkg->tagline,
                'price' => $pkg->price,
                'gallery_days' => $pkg->gallery_days,
                'max_frames' => $pkg->max_frames,
                'allow_zip_download' => $pkg->allow_zip_download,
                'allow_custom_theme' => $pkg->allow_custom_theme,
                'allow_video' => $pkg->allow_video,
                'max_video_seconds' => $pkg->resolvedVideoMaxSeconds(),
                'benefits' => $pkg->benefits ?? [],
                'used_by' => $activeUsage->get($pkg->id),
            ]);

        return Inertia::render('client/packages/Index', [
            'packages' => $availablePackages,
        ]);
    }
}
