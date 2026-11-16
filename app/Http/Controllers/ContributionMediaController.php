<?php

namespace App\Http\Controllers;

use App\Enums\GalleryVisibility;
use App\Models\Contribution;
use App\Support\EventAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContributionMediaController extends Controller
{
    public function __invoke(Request $request, Contribution $contribution, string $asset = 'file'): BinaryFileResponse|RedirectResponse
    {
        $event = $contribution->event;
        abort_unless($event !== null, 404);
        $public = $contribution->visibility === 'public'
            && $contribution->is_approved
            && $event->gallery_visibility !== GalleryVisibility::Private;
        $owner = $contribution->is_approved && EventAccess::canViewPrivate($request, $event);
        abort_unless($public || $owner || $request->user()?->is_admin, 404);
        $path = $asset === 'thumbnail' ? $contribution->thumbnail_path : $contribution->file_path;
        abort_unless((bool) $path, 404);

        if ($contribution->file_disk === 'r2') {
            return redirect(Storage::disk('r2')->url($path));
        }

        $disk = Storage::disk($contribution->file_disk);
        abort_unless($disk->exists($path), 404);

        $headers = [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($asset === 'file' && $contribution->mime_type) {
            $headers['Content-Type'] = $contribution->mime_type;
        }

        return response()->file($disk->path($path), $headers)
            ->setContentDisposition($request->boolean('download') ? 'attachment' : 'inline');
    }
}
