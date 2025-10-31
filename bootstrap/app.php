<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Fix for open_basedir: Ensure vendor exception views directory exists early
// This runs before Laravel initializes to prevent path checking errors
$vendorViewsPath = __DIR__ . '/../resources/views/vendor';
$exceptionViewsPath = $vendorViewsPath . '/laravel-exceptions';

if (!@is_dir($vendorViewsPath)) {
    @mkdir($vendorViewsPath, 0755, true);
}
if (!@is_dir($exceptionViewsPath)) {
    @mkdir($exceptionViewsPath, 0755, true);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
