<?php

namespace App\Http\Controllers\Admin;

use App\Events\EventStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Package;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminEventController extends Controller
{
    public function index(): Response
    {
        // Sinkronkan status otomatis semua acara non-manual sebelum ditampilkan
        Event::syncAllAutomaticStatuses();

        $events = Event::with(['package:id,name', 'theme:id,name', 'user:id,name,email'])
            ->withCount('contributions')
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/events/Index', [
            'events' => $events,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/events/Create', [
            'packages' => Package::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'price', 'gallery_days', 'max_frames', 'allow_zip_download', 'allow_custom_theme', 'allow_video', 'max_video_seconds']),
            'themes' => Theme::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'mood', 'primary_color', 'accent_color', 'neutral_color']),
            'clients' => User::where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'package_id' => ['required', 'uuid', 'exists:packages,id'],
            'theme_id' => ['nullable', 'uuid', 'exists:themes,id'],
            'use_custom_colors' => ['sometimes', 'boolean'],
            'custom_colors' => ['nullable', 'array'],
            'custom_colors.primary' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'custom_colors.accent' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'custom_colors.neutral' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'category' => ['required', 'in:wedding,birthday,corporate,other'],
            'party_one_name' => ['required', 'string', 'max:100'],
            'party_two_name' => ['nullable', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'venue' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'gallery_visibility' => ['required', 'in:public,private'],
            'is_demo' => ['boolean'],
            'cover' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:8192'],
        ]);

        $this->sanitizeThemeForPackage($data, $request);
        $data['is_manual_status'] = $request->boolean('is_manual_status', false);
        if ($data['is_manual_status'] && $request->filled('status')) {
            $data['status'] = $request->input('status');
        } else {
            $data['status'] = 'draft'; // saving booted hook will auto-resolve based on event_date
        }
        unset($data['cover']);
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('event-covers', 'public');
        }
        $data['slug'] = $this->uniqueSlug(
            $data['party_one_name'],
            $data['party_two_name'] ?? null,
            $data['event_date'] ?? null,
        );
        $event = Event::create($data);

        event(new EventStatusUpdated($event->fresh(['package', 'theme'])));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Acara berhasil dibuat.']);

        return redirect()->route('events.edit', $event);
    }

    public function edit(Event $event): Response
    {
        $event->syncStatus();

        return Inertia::render('admin/events/Edit', [
            'event' => array_merge($event->load(['package:id,name', 'theme:id,name', 'user:id,name,email'])->toArray(), [
                'event_date' => $event->event_date?->toDateString(),
                'cover_url' => $event->cover_path ? asset('storage/'.$event->cover_path) : null,
                'is_manual_status' => (bool) $event->is_manual_status,
            ]),
            'ownerUrl' => route('event.owner', ['event' => $event, 'token' => $event->owner_access_token]),
            'guestUrl' => route('event.show', $event),
            'eventColors' => $event->colors(),
            'packages' => Package::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'price', 'gallery_days', 'max_frames', 'allow_zip_download', 'allow_custom_theme', 'allow_video', 'max_video_seconds']),
            'themes' => Theme::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'mood', 'primary_color', 'accent_color', 'neutral_color']),
            'clients' => User::where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'package_id' => ['required', 'uuid', 'exists:packages,id'],
            'theme_id' => ['nullable', 'uuid', 'exists:themes,id'],
            'use_custom_colors' => ['sometimes', 'boolean'],
            'custom_colors' => ['nullable', 'array'],
            'custom_colors.primary' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'custom_colors.accent' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'custom_colors.neutral' => ['required_with:custom_colors', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'category' => ['required', 'in:wedding,birthday,corporate,other'],
            'status' => ['required', 'in:draft,active,closed,expired'],
            'is_manual_status' => ['sometimes', 'boolean'],
            'party_one_name' => ['required', 'string', 'max:100'],
            'party_two_name' => ['nullable', 'string', 'max:100'],
            'event_date' => ['nullable', 'date'],
            'venue' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'gallery_visibility' => ['required', 'in:public,private'],
            'is_demo' => ['boolean'],
            'cover' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:8192'],
            'remove_cover' => ['sometimes', 'boolean'],
        ]);

        $this->sanitizeThemeForPackage($data, $request);
        $oldCover = $event->cover_path;
        unset($data['cover'], $data['remove_cover']);
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('event-covers', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $data['cover_path'] = null;
        }

        if ($request->has('is_manual_status')) {
            $data['is_manual_status'] = $request->boolean('is_manual_status');
        } else {
            $data['is_manual_status'] = true;
        }

        if (! $data['is_manual_status']) {
            $event->is_manual_status = false;
            $data['status'] = $event->resolveAutomaticStatus();
        }

        $event->update($data);
        if ($oldCover && $oldCover !== $event->cover_path) {
            Storage::disk('public')->delete($oldCover);
        }

        event(new EventStatusUpdated($event->fresh(['package', 'theme'])));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Acara berhasil diperbarui.']);

        return back();
    }

    public function toggleStatus(Request $request, Event $event): RedirectResponse
    {
        $action = $request->string('action')->toString();

        if ($action === 'auto') {
            $event->update(['is_manual_status' => false]);
            $event->syncStatus();
            $label = $event->status->label();
            Inertia::flash('toast', ['type' => 'success', 'message' => "Status acara dikembalikan ke otomatis ({$label})."]);
        } else {
            $status = $request->string('status')->toString();
            if (in_array($status, ['active', 'closed', 'draft', 'expired'], true)) {
                $event->update([
                    'status' => $status,
                    'is_manual_status' => true,
                ]);
                $label = $event->status->label();
                Inertia::flash('toast', ['type' => 'success', 'message' => "Status acara diatur manual menjadi {$label}."]);
            }
        }

        event(new EventStatusUpdated($event->fresh(['package', 'theme'])));

        return back();
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Acara berhasil dihapus.']);

        return redirect()->route('events.index');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sanitizeThemeForPackage(array &$data, Request $request): void
    {
        if ($request->has('use_custom_colors') && ! $request->boolean('use_custom_colors')) {
            $data['custom_colors'] = null;
        }
        unset($data['use_custom_colors']);

        $package = Package::whereKey($data['package_id'])->first();
        if (! $package?->allow_custom_theme) {
            $data['theme_id'] = null;
            $data['custom_colors'] = null;
        }
    }

    private function uniqueSlug(string $partyOne, ?string $partyTwo, ?string $eventDate): string
    {
        $year = $eventDate ? Carbon::parse($eventDate)->year : now()->year;
        $names = collect([$partyOne, $partyTwo])->filter()->implode('-');
        $base = Str::substr(Str::slug($names.'-'.$year), 0, 100);
        $slug = $base;
        $sequence = 2;

        while (Event::where('slug', $slug)->exists()) {
            $suffix = '-'.$sequence;
            $slug = Str::substr($base, 0, 100 - Str::length($suffix)).$suffix;
            $sequence++;
        }

        return $slug;
    }
}
