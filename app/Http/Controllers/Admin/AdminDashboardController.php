<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\Package;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
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
}
