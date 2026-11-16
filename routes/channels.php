<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('events.{eventId}.owner', function (User $user, string $eventId) {
    $event = Event::find($eventId);

    return $event && ((string) $user->id === (string) $event->user_id || (bool) $user->is_admin);
});
