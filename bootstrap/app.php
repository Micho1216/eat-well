<?php

use App\Http\Middleware\AccountSetup\EnsureAddressExists;
use App\Http\Middleware\AccountSetup\EnsureNoAddressExist;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LogUserActivity;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetLocale::class,
            // LogUserActivity::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'ensureAddress' => EnsureAddressExists::class,
            'ensureNoAddress' => EnsureNoAddressExist::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
