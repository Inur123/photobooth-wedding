<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $r2Url = rtrim((string) config('filesystems.disks.r2.url'), '/');
        $mediaOrigin = $r2Url !== '' ? ' '.$r2Url : '';

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(self)');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:{$mediaOrigin}; media-src 'self' blob:{$mediaOrigin}; font-src 'self' data:; connect-src 'self' ws: wss:{$mediaOrigin}; frame-ancestors 'none'"
        );

        return $response;
    }
}
