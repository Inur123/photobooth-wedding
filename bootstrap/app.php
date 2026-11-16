<?php

use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ValidateTurnstile;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            SecurityHeaders::class,
            HandleAppearance::class,
            EnsureUserIsActive::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            ValidateTurnstile::class,
        ]);

        $middleware->alias([
            'admin' => EnsureIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $msg = 'Ukuran file atau video terlalu besar melebihi batas server (maksimal 25MB). Silakan gunakan file yang lebih ringkas.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 413);
            }

            return back()->withErrors(['file' => $msg])->with('error', $msg);
        });

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $response;
            }

            $statusCode = $response->getStatusCode();

            // Retain Ignition debug screen in local development when debug is true for 500 error
            if (config('app.debug') && $statusCode === 500) {
                return $response;
            }

            if (in_array($statusCode, [401, 403, 404, 419, 429, 500, 503])) {
                if ($statusCode === 419 && $request->header('X-Inertia') && ! $request->isMethod('GET')) {
                    return back()->withErrors([
                        'message' => 'Sesi Anda telah kedaluwarsa, silakan coba lagi.',
                    ])->with('error', 'Sesi Anda telah kedaluwarsa, silakan coba lagi.');
                }

                return Inertia::render('Error', [
                    'status' => $statusCode,
                    'message' => ! app()->environment('production') ? $e->getMessage() : null,
                ])
                    ->toResponse($request)
                    ->setStatusCode($statusCode);
            }

            return $response;
        });
    })->create();
