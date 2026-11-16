<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContributionCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Contribution $contribution) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $event = $this->contribution->event;
        if (! $event) {
            return [];
        }

        return [
            new Channel('events.'.$event->slug),
            new Channel('events.'.$event->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ContributionCreated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'contribution' => [
                'id' => $this->contribution->id,
                'type' => $this->contribution->type->value,
                'layout' => $this->contribution->layout,
                'guest_name' => $this->contribution->guest_name,
                'visibility' => $this->contribution->visibility,
                'message' => $this->contribution->message,
                'file_url' => $this->contribution->file_url,
                'thumbnail_url' => $this->contribution->thumbnail_url,
                'duration_seconds' => $this->contribution->duration_seconds,
                'created_at' => $this->contribution->created_at?->toISOString() ?? now()->toISOString(),
                'width' => $this->contribution->width,
                'height' => $this->contribution->height,
                'dominant_color' => $this->contribution->dominant_color,
                'processing_status' => $this->contribution->processing_status,
                'is_approved' => (bool) $this->contribution->is_approved,
            ],
        ];
    }
}
