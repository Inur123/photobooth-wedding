<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Berhasil masuk.']);

        return redirect()->intended(Fortify::redirects('login'));
    }
}
