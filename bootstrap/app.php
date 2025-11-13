<?php

use App\Http\Middleware\AdminMiddleware;
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
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log 403 errors for broadcasting routes to help debug
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 403 && $request->is('broadcasting/*')) {
                \Log::warning('Broadcasting route 403 error', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user' => auth()->user()?->id,
                    'authenticated' => auth()->check(),
                    'csrf_token_header' => $request->header('X-CSRF-TOKEN'),
                    'session_csrf_token' => csrf_token(),
                    'request_body' => $request->all(),
                    'channel_name' => $request->input('channel_name'),
                    'socket_id' => $request->input('socket_id'),
                    'headers' => $request->headers->all(),
                ]);
            }
        });
    })->create();
