<?php

use App\Http\Middleware\CheckOrgPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureOrgVerified;
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
        $middleware->alias([
            'role' => CheckRole::class,
            'org.permission' => CheckOrgPermission::class,
            'verified.org' => EnsureOrgVerified::class,
        ]);

        // Exclude T-Bank webhook from CSRF verification (bank sends POST without CSRF token)
        $middleware->validateCsrfTokens(except: [
            'payments/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
