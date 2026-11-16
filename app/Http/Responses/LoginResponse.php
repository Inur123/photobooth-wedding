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

        if (! $request->user()?->hasVerifiedEmail()) {
            Inertia::flash('toast', [
                'type' => 'warning',
                'message' => 'Email belum diverifikasi. Silakan cek inbox email kamu untuk mengaktifkan akun.',
            ]);

            return redirect()->route('verification.notice');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Berhasil masuk.']);

        return redirect()->intended(Fortify::redirects('login'));
    }
}
