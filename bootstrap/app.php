<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind nginx/cPanel reverse proxy so HTTPS and assets resolve correctly
        $middleware->trustProxies(at: '*');

        // Stripe webhook must NOT have CSRF protection — Stripe sends raw POST
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'admin'       => \App\Http\Middleware\IsAdmin::class,
            'free.trial'  => \App\Http\Middleware\FreeTrialLimit::class,
            'play.access' => \App\Http\Middleware\EnsurePlayAccess::class,
            'subscribed'  => \App\Http\Middleware\EnsureSubscribed::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
