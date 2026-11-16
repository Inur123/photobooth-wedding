<?php

namespace App\Http\Controllers;

use App\Events\ContributionDeleted;
use App\Events\ContributionUpdated;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ClientEventController extends Controller
{
    public function index(Request $request): Response
    {
        $events = Event::where('user_id', $request->user()->id)
            ->with(['package', 'theme'])
            ->withCount('contributions')
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
                'venue' => $event->venue,
                'status' => $event->status->value,
                'contributions_count' => $event->contributions_count,
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
                'colors' => $event->colors(),
                'guest_url' => route('event.show', $event),
            ]);

        return Inertia::render('client/events/Index', [
            'userEvents' => $events,
        ]);
    }

    public function show(Request $request, Event $event): Response
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        $event->load(['package', 'theme']);

        return Inertia::render('client/events/Show', [
            'event' => array_merge($event->toArray(), [
                'event_date' => $event->event_date?->toDateString(),
                'cover_url' => $event->cover_path ? asset('storage/'.$event->cover_path) : null,
                'status_label' => $event->status->value,
            ]),
            'package' => $event->package,
            'themes' => Theme::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'mood', 'primary_color', 'accent_color', 'neutral_color']),
            'guestUrl' => route('event.show', $event),
            'galleryUrl' => route('event.gallery', $event),
            'ownerUrl' => route('event.owner', ['event' => $event, 'token' => $event->owner_access_token]),
            'eventColors' => $event->colors(),
            'allowedLayouts' => $event->package->allowedPhotoLayouts(),
            'stats' => (function () use ($event) {
                $counts = $event->contributions()
                    ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved, SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as hidden')
                    ->first();

                return [
                    'total' => (int) ($counts->total ?? 0),
                    'approved' => (int) ($counts->approved ?? 0),
                    'hidden' => (int) ($counts->hidden ?? 0),
                ];
            })(),
            'contributions' => $event->contributions()
                ->latest()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'party_one_name' => ['required', 'string', 'max:100'],
            'party_two_name' => ['nullable', 'string', 'max:100'],
            'venue' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'gallery_visibility' => ['required', 'in:public,private'],
            'theme_id' => ['nullable', 'uuid', 'exists:themes,id'],
            'use_custom_colors' => ['sometimes', 'boolean'],
            'custom_colors' => ['nullable', 'array'],
            'custom_colors.primary' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'custom_colors.accent' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'custom_colors.neutral' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cover' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:8192'],
            'remove_cover' => ['sometimes', 'boolean'],
        ]);

        if ($request->has('use_custom_colors') && ! $request->boolean('use_custom_colors')) {
            $data['custom_colors'] = null;
        }
        unset($data['use_custom_colors']);

        $event->loadMissing('package');
        if (! $event->package?->allow_custom_theme) {
            $data['theme_id'] = null;
            $data['custom_colors'] = null;
        }

        $oldCover = $event->cover_path;
        unset($data['cover'], $data['remove_cover']);

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('event-covers', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $data['cover_path'] = null;
        }

        // Client cannot change event_date, package_id, status, is_demo, or slug!
        $event->update($data);

        if ($oldCover && $oldCover !== $event->cover_path) {
            Storage::disk('public')->delete($oldCover);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Detail acaramu berhasil diperbarui.']);

        return back();
    }

    public function toggleContribution(Request $request, Event $event, Contribution $contribution): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id && $contribution->event_id === $event->id, 403);

        $contribution->update([
            'is_approved' => ! $contribution->is_approved,
        ]);

        event(new ContributionUpdated($contribution->fresh('event')));

        $status = $contribution->is_approved ? 'ditampilkan' : 'disembunyikan';
        Inertia::flash('toast', ['type' => 'success', 'message' => "Momen berhasil {$status} di galeri."]);

        return back();
    }

    public function destroyContribution(Request $request, Event $event, Contribution $contribution): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id && $contribution->event_id === $event->id, 403);

        $disk = $contribution->file_disk ?? 'public';
        if ($contribution->file_path && Storage::disk($disk)->exists($contribution->file_path)) {
            Storage::disk($disk)->delete($contribution->file_path);
        }
        if ($contribution->thumbnail_path && Storage::disk($disk)->exists($contribution->thumbnail_path)) {
            Storage::disk($disk)->delete($contribution->thumbnail_path);
        }

        $contributionId = $contribution->id;
        $eventSlug = $event->slug;
        $eventId = $event->id;

        $contribution->delete();

        event(new ContributionDeleted($contributionId, $eventSlug, $eventId));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Momen berhasil dihapus permanen.']);

        return back();
    }

    public function downloadZip(Request $request, Event $event): BinaryFileResponse
    {
        abort_unless($event->user_id === $request->user()->id || $request->user()->is_admin, 403);
        abort_unless($event->package?->allow_zip_download, 403, 'Paket acara ini belum mendukung unduh arsip ZIP.');

        $contributions = $event->contributions()
            ->whereNotNull('file_path')
            ->where('is_approved', true)
            ->get();

        abort_if($contributions->isEmpty(), 404, 'Belum ada foto atau momen yang disetujui untuk diunduh.');

        $zipPath = (string) tempnam(sys_get_temp_dir(), 'moment_zip_');
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat arsip ZIP.');
        }

        foreach ($contributions as $idx => $c) {
            $disk = $c->file_disk ?? 'public';
            if ($c->file_path && Storage::disk($disk)->exists($c->file_path)) {
                $ext = pathinfo($c->file_path, PATHINFO_EXTENSION) ?: 'jpg';
                $name = sprintf('%03d_%s_%s.%s', $idx + 1, $c->type->value, Str::slug($c->guest_name ?: 'tamu'), $ext);
                $zip->addFromString($name, (string) Storage::disk($disk)->get($c->file_path));
            }
        }
        $zip->close();

        $filename = sprintf('momen_%s.zip', $event->slug);

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}
