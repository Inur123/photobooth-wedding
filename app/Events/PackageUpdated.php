<?php

namespace App\Events;

use App\Models\Package;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ?Package $package = null) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('packages'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PackageUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $activePackages = Package::where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'slug',
                'tagline',
                'price',
                'benefits',
                'gallery_days',
                'max_frames',
                'allow_zip_download',
                'allow_custom_theme',
                'allow_video',
                'max_video_seconds',
            ]);

        return [
            'packages' => $activePackages->toArray(),
            'updated_package_id' => $this->package?->id,
        ];
    }
}
