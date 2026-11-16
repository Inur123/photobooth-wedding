<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Berhasil keluar.']);

        return redirect(Fortify::redirects('logout', '/'));
    }
}
