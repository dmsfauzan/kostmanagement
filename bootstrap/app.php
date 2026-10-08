<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetRequestId;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetRequestId::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e) {
            return match ($e->getStatusCode()) {
                403 => response()->view('errors.403', [], 403),
                404 => response()->view('errors.404', [], 404),
                419 => response()->view('errors.419', [], 419),
                429 => response()->view('errors.429', [], 429),
                default => null,
            };
        });

        $exceptions->render(function (Throwable $e) {
            if (app()->hasDebugModeEnabled()) {
                return null;
            }

            report($e);

            return response()->view('errors.500', [
                'requestId' => request()->attributes->get('request_id'),
            ], 500);
        });
    })->create();
