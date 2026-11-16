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

        $devOrigins = '';
        if (app()->environment('local', 'testing')) {
            $devOrigins = ' http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*';
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(self)');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'{$devOrigins}; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://challenges.cloudflare.com{$devOrigins}; frame-src 'self' https://challenges.cloudflare.com; style-src 'self' 'unsafe-inline'{$devOrigins}; img-src 'self' data: blob:{$devOrigins}{$mediaOrigin}; media-src 'self' blob:{$devOrigins}{$mediaOrigin}; font-src 'self' data:{$devOrigins}; connect-src 'self' ws: wss: https://challenges.cloudflare.com{$devOrigins}{$mediaOrigin}; frame-ancestors 'none'"
        );

        return $response;
    }
}
