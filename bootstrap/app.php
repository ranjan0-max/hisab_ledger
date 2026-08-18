<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission'   => \App\Http\Middleware\CheckPermission::class,
            'superadmin'   => \App\Http\Middleware\SuperAdminOnly::class,
            'session.timeout' => \App\Http\Middleware\CheckSessionTimeout::class,
        ]);

        // Apply per-client session timeout check to all web routes
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckSessionTimeout::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return redirect()->route('login')->with('error', 'Session expired due to inactivity. Please log in again.');
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
            }
        });

    })->create();
