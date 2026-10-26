<?php

use App\Http\Middleware\DisableInertiaSsr;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IdleTimeout;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'idle' => IdleTimeout::class,
            'locale' => SetLocale::class,
            'inertia' => HandleInertiaRequests::class,
            'no-ssr' => DisableInertiaSsr::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $exception->status === 429 ? 'TOO_MANY_REQUESTS' : 'VALIDATION_FAILED',
                    'message' => __('auth.validation_failed'),
                    'details' => $exception->errors(),
                ],
            ], $exception->status);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => __('auth.unauthenticated'),
                    'details' => [],
                ],
            ], 401);
        });
    })->create();
