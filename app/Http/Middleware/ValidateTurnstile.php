<?php

namespace App\Http\Middleware;

use App\Rules\TurnstileRule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTurnstile
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secretKey = (string) config('services.turnstile.secret_key');

        if ($secretKey === '') {
            return $next($request);
        }

        // Pada testing environment, izinkan request tanpa token kecuali secara eksplisit diuji
        if (app()->environment('testing') && ! $request->has('cf-turnstile-response')) {
            return $next($request);
        }

        if ($request->isMethod('POST') && $request->is('login', 'register', 'forgot-password')) {
            $request->validate([
                'cf-turnstile-response' => [new TurnstileRule],
            ]);
        }

        return $next($request);
    }
}
