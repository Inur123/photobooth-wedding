<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Event;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        if ($request->user()?->is_admin) {
            return Inertia::render('admin/Dashboard', [
                'stats' => [
                    'total_events' => Event::count(),
                    'active_events' => Event::where('status', 'active')->count(),
                    'total_contributions' => Contribution::count(),
                    'pending_approvals' => Contribution::where('is_approved', false)->count(),
                    'total_packages' => Package::where('is_active', true)->count(),
                ],
                'recent_events' => Event::with(['package:id,name', 'theme:id,name'])
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'slug', 'party_one_name', 'party_two_name', 'status', 'event_date', 'package_id', 'theme_id']),
            ]);
        }

        $events = Event::where('user_id', $request->user()->id)
            ->with(['package:id,name', 'theme:id,name'])
            ->withCount('contributions')
            ->latest()
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'slug' => $event->slug,
                'party_one_name' => $event->party_one_name,
                'party_two_name' => $event->party_two_name,
                'event_date' => $event->event_date?->toDateString(),
                'venue' => $event->venue,
                'status' => $event->status->value,
                'package_name' => $event->package->name,
                'contribution_count' => $event->contributions_count,
            ]);

        return Inertia::render('Dashboard', [
            'events' => $events,
        ]);
    }
}
