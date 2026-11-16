<?php

namespace App\Http\Controllers\Admin;

use App\Events\ContributionDeleted;
use App\Events\ContributionUpdated;
use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

use function in_array;

class AdminContributionController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->string('filter', 'all')->toString();
        if (! in_array($filter, ['all', 'approved', 'hidden'], true)) {
            $filter = 'all';
        }

        $selectedEventId = $request->string('event')->toString() ?: null;

        $events = Event::query()
            ->withCount([
                'contributions as total_contributions_count',
                'contributions as approved_contributions_count' => fn ($q) => $q->where('is_approved', true),
                'contributions as hidden_contributions_count' => fn ($q) => $q->where('is_approved', false),
            ])
            ->latest()
            ->get(['id', 'slug', 'party_one_name', 'party_two_name', 'event_date', 'venue', 'status', 'cover_path'])
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'slug' => $event->slug,
                'party_one_name' => $event->party_one_name,
                'party_two_name' => $event->party_two_name,
                'event_date' => $event->event_date?->toDateString(),
                'venue' => $event->venue,
                'status' => $event->status->value,
                'cover_url' => $event->cover_path ? asset('storage/'.$event->cover_path) : null,
                'total_count' => (int) ($event->total_contributions_count ?? 0),
                'approved_count' => (int) ($event->approved_contributions_count ?? 0),
                'hidden_count' => (int) ($event->hidden_contributions_count ?? 0),
            ]);

        $query = Contribution::with(['event:id,slug,party_one_name,party_two_name'])
            ->when($selectedEventId, fn ($q) => $q->where('event_id', $selectedEventId))
            ->latest();

        if ($filter === 'approved') {
            $query->where('is_approved', true);
        } elseif ($filter === 'hidden') {
            $query->where('is_approved', false);
        }

        $contributions = $query->paginate(20)->withQueryString();

        $baseCountQuery = Contribution::query()
            ->when($selectedEventId, fn ($q) => $q->where('event_id', $selectedEventId));

        return Inertia::render('admin/contributions/Index', [
            'events' => $events,
            'selectedEventId' => $selectedEventId,
            'contributions' => $contributions,
            'filter' => $filter,
            'counts' => [
                'all' => (clone $baseCountQuery)->count(),
                'approved' => (clone $baseCountQuery)->where('is_approved', true)->count(),
                'hidden' => (clone $baseCountQuery)->where('is_approved', false)->count(),
            ],
        ]);
    }

    public function approve(Contribution $contribution): RedirectResponse
    {
        $contribution->update(['is_approved' => true]);

        event(new ContributionUpdated($contribution->fresh('event')));

        Inertia::flash('toast', ['type' => 'success', 'message' => $contribution->visibility === 'private' ? 'Kiriman disetujui untuk galeri pribadi pemilik acara.' : 'Kiriman ditampilkan di galeri.']);

        return back();
    }

    public function reject(Contribution $contribution): RedirectResponse
    {
        $contribution->update(['is_approved' => false]);

        event(new ContributionUpdated($contribution->fresh('event')));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kiriman disembunyikan dari galeri.']);

        return back();
    }

    public function destroy(Contribution $contribution): RedirectResponse
    {
        if ($contribution->file_path) {
            Storage::disk($contribution->file_disk)->delete($contribution->file_path);
        }
        if ($contribution->thumbnail_path) {
            Storage::disk($contribution->file_disk)->delete($contribution->thumbnail_path);
        }

        $id = $contribution->id;
        $event = $contribution->event;

        $contribution->delete();

        if ($event) {
            event(new ContributionDeleted($id, $event->slug, $event->id));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kiriman berhasil dihapus.']);

        return back();
    }
}
