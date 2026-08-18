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
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
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

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                $message = $e->getMessage() ?: 'An unexpected error occurred. Please try again.';
                return redirect()->back()->with('error', $message);
            }
        });
    })->create();
