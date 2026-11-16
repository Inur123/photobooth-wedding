<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Http\Request;

class EventAccess
{
    public static function canViewPrivate(Request $request, Event $event): bool
    {
        if ($request->query('preview') === 'guest' || $request->query('as') === 'guest') {
            return false;
        }

        return self::hasOwnerPrivilege($request, $event);
    }

    public static function hasOwnerPrivilege(Request $request, Event $event): bool
    {
        if ($request->user()?->is_admin) {
            return true;
        }

        if ($request->user() && $request->user()->id === $event->user_id) {
            return true;
        }

        $access = $request->session()->get('event_owner.'.$event->id);

        return $event->owner_access_token !== null && is_string($access)
            && hash_equals(hash('sha256', $event->owner_access_token), $access);
    }
}
