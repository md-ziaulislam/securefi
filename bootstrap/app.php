<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        $middleware->web(append: [
            \App\Http\Middleware\RedirectMiddleware::class,
            \App\Http\Middleware\VisitorTrackingMiddleware::class,
            \App\Http\Middleware\SetTimezone::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Real-Time Error Monitoring (Sentry - PRD-ADDNEW 2.1)
        $exceptions->report(function (\Throwable $e) {
            try {
                $sentry = app(\App\Services\ErrorMonitoringService::class);
                if ($sentry->isEnabled()) {
                    $sentry->captureException($e);
                }
            } catch (\Throwable $ignored) {
                // Safeguard against error logging recursion
            }
        });
    })->create();
