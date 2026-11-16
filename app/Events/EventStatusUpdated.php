<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Event $event) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('events.'.$this->event->slug),
            new Channel('events.'.$this->event->id),
            new Channel('admin.events'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'EventStatusUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'event_id' => $this->event->id,
            'slug' => $this->event->slug,
            'status' => $this->event->status->value,
            'is_open' => $this->event->isOpen(),
            'is_manual_status' => (bool) $this->event->is_manual_status,
        ];
    }
}
