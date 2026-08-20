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
        // Backpack list search endpoints are POST requests used for read-only filtering.
        // Exempting only these routes prevents 419 errors while preserving CSRF checks elsewhere.
        $middleware->validateCsrfTokens(except: [
            'admin/*/search',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
