<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pendaftaran akun berhasil! Selamat datang di titip.moment.',
        ]);

        return redirect()->intended(Fortify::redirects('register'));
    }
}
