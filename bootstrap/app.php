<?php

use App\Exceptions\RateLimiterException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use jeremykenedy\LaravelRoles\App\Exceptions\RoleDeniedException;
use App\Http\Middleware\HandleInertiaRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'redirect-to-dashboard' => \App\Http\Middleware\RedirectToDashboard::class,
            'role' => \jeremykenedy\LaravelRoles\App\Http\Middleware\VerifyRole::class,
        ]);
        $middleware->group('universal', []);
        $middleware->trustProxies(at: [
            '127.0.0.1',
            'localhost',
            '192.168.1.1',
            '10.0.0.0/8',
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (RoleDeniedException $exception) {
            return response()->view('errors.403', [], 403);
        });

        $exceptions->report(function (Exception $exception) {
            if ($exception instanceof RateLimiterException) {
                Log::warning(message: 'Rate limit exceeded.', context: ['ip' => request()->ip(), 'url' => request()->fullUrl()]);
                return response()->view('errors.429', [], 429);
            }
        });
    })->create();
