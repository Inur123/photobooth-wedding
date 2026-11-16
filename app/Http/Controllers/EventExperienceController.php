<?php

namespace App\Http\Controllers;

use App\Enums\ContributionType;
use App\Enums\GalleryVisibility;
use App\Events\ContributionCreated;
use App\Http\Requests\StoreContributionRequest;
use App\Jobs\ProcessContributionMedia;
use App\Models\Event;
use App\Support\EventAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventExperienceController extends Controller
{
    public function showOwner(Request $request, Event $event, string $token, string $section = 'album'): Response
    {
        abort_unless($event->owner_access_token && hash_equals($event->owner_access_token, $token), 404);

        $request->session()->put('event_owner.'.$event->id, hash('sha256', $token));

        return Inertia::render('event/Show', $this->experiencePayload(
            $request,
            $event,
            $section,
            isOwner: true,
            ownerToken: $token,
        ));
    }

    public function show(Request $request, Event $event, string $section = 'cover'): Response
    {
        return Inertia::render('event/Show', $this->experiencePayload(
            $request,
            $event,
            $section,
            isOwner: false,
            ownerToken: null,
        ));
    }

    public function store(StoreContributionRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();
        $type = ContributionType::from($data['type']);

        // Idempotency: jika client_upload_id sudah ada, kembalikan sukses tanpa buat ulang
        $clientUploadId = $request->input('client_upload_id');
        if ($clientUploadId) {
            $existing = $event->contributions()->where('client_upload_id', $clientUploadId)->first();
            if ($existing) {
                Inertia::flash('toast', ['type' => 'success', 'message' => 'Momenmu sudah berhasil dititipkan.']);

                return back();
            }
        }

        $contribution = [
            'type' => $type,
            'guest_name' => $data['guest_name'],
            'visibility' => $event->gallery_visibility === GalleryVisibility::Private ? 'private' : $data['visibility'],
            'file_disk' => 'r2',
            'frame_id' => $data['frame_id'] ?? null,
            'layout' => $data['layout'] ?? null,
            'message' => $data['message'] ?? null,
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'client_upload_id' => $clientUploadId ?: null,
            'is_approved' => true,
            // Status awal: foto akan diproses oleh queue, lainnya langsung done
            'processing_status' => $type === ContributionType::Photo ? 'pending' : 'done',
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $detectedMime = $file->getMimeType();
            $mimeType = ($type === ContributionType::Voice && str_starts_with($detectedMime, 'video/'))
                ? 'audio/'.substr($detectedMime, 6)
                : $detectedMime;
            $contribution += [
                'file_path' => $file->store("photobooth/events/{$event->id}/{$type->value}", 'r2'),
                'mime_type' => $mimeType,
                'size_bytes' => $file->getSize(),
            ];
        }

        $newContribution = $event->contributions()->create($contribution);

        // Dispatch kompresi foto ke queue (async, tidak memblokir respons)
        if ($type === ContributionType::Photo && $newContribution->file_path) {
            ProcessContributionMedia::dispatch($newContribution);
        }

        // Siarkan secara real-time via Laravel Reverb (foto/video/wish langsung muncul di layar)
        if ($newContribution->is_approved) {
            event(new ContributionCreated($newContribution->load('event')));
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $contribution['visibility'] === 'private'
                ? 'Momen privat berhasil dititipkan untuk pemilik acara.'
                : 'Terima kasih! Momenmu berhasil dititipkan.',
        ]);

        return back();
    }

    /** Public (or admin-only when private) gallery of approved contributions. */
    public function gallery(Request $request, Event $event): Response
    {
        abort_if(
            $event->gallery_visibility === GalleryVisibility::Private && ! EventAccess::canViewPrivate($request, $event),
            403,
        );

        $isOwner = EventAccess::canViewPrivate($request, $event);

        return Inertia::render('event/Gallery', $this->experiencePayload(
            $request,
            $event,
            'album',
            isOwner: $isOwner,
        ));
    }

    /** @return array<string, mixed> */
    private function experiencePayload(
        Request $request,
        Event $event,
        string $section,
        bool $isOwner = false,
        ?string $ownerToken = null,
    ): array {
        $event->syncStatus();
        $event->load(['theme', 'package']);
        $canViewPrivate = $isOwner || (bool) $request->user()?->is_admin;
        $canViewGallery = $event->gallery_visibility !== GalleryVisibility::Private || $canViewPrivate;
        $allowVideo = (bool) ($event->package->allow_video ?? true);

        return [
            'navigation' => $this->navigationPayload($request, $section, $allowVideo),
            'event' => $this->eventPayload($event),
            'isOpen' => $event->isOpen(),
            'canViewGallery' => (bool) $canViewGallery,
            'canViewPrivate' => $canViewPrivate,
            'ownerToken' => $ownerToken,
            'frames' => [],
            'contributions' => fn () => $canViewGallery
                ? Inertia::scroll(
                    $event->contributions()
                        ->where('is_approved', true)
                        ->latest('created_at')
                        ->latest('id')
                        ->when(! $canViewPrivate, fn ($query) => $query->where('visibility', 'public'))
                        ->cursorPaginate(15, ['id', 'type', 'layout', 'guest_name', 'message', 'visibility', 'file_disk', 'file_path', 'thumbnail_path', 'duration_seconds', 'created_at', 'width', 'height', 'dominant_color', 'processing_status'])
                )->configureMergeIntent()->matchOn('data.id')
                : [],
        ];
    }

    /** @return array<string, string> */
    private function navigationPayload(Request $request, string $section, bool $allowVideo = true): array
    {
        $tabs = ['album' => 'album', 'reels' => 'reels', 'catatan' => 'notes', 'acara' => 'info'];
        $from = $request->query('from');
        $tab = $section === 'titip' ? ($tabs[is_string($from) ? $from : ''] ?? 'album') : ($tabs[$section] ?? 'album');
        if (! $allowVideo && $tab === 'reels') {
            $tab = 'album';
        }
        $filter = $request->query('filter');

        return [
            'section' => $section,
            'tab' => $tab,
            'view' => $request->query('view') === 'grid' ? 'grid' : 'feed',
            'filter' => in_array($filter, ['wish', 'voice'], true) ? $filter : 'all',
        ];
    }

    /** @return array<string, mixed> */
    private function eventPayload(Event $event): array
    {
        $package = $event->package;

        return [
            'slug' => $event->slug,
            'category' => $event->category,
            'party_one_name' => $event->party_one_name,
            'party_two_name' => $event->party_two_name,
            'event_date' => $event->event_date?->toDateString(),
            'venue' => $event->venue,
            'notes' => $event->notes,
            'colors' => $event->colors(),
            'cover_url' => $event->cover_path ? asset('storage/'.$event->cover_path) : null,
            'gallery_visibility' => $event->gallery_visibility,
            'theme' => $event->supportsCustomTheme() ? ($event->theme->slug ?? 'titip-momen') : 'titip-momen',
            'package_name' => $package?->name,
            'capabilities' => [
                'types' => $package ? $package->allowedContributionTypes() : ['photo', 'voice', 'wish'],
                'photo_layouts' => $package ? $package->allowedPhotoLayouts() : [1],
                'max_duration_seconds' => 60,
                'max_video_seconds' => $package ? $package->resolvedVideoMaxSeconds() : 0,
                'max_video_kb' => $package ? $package->resolvedVideoMaxKb() : 0,
                'max_photo_kb' => 4096,
                'max_voice_kb' => 5120,
                'max_voice_seconds' => 60,
                'max_frames' => $package->max_frames ?? 1,
            ],
        ];
    }
}
