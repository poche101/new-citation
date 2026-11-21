<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Guests who open an admin page (dashboard, exports, downloads) go to
        // the admin login instead of hitting an error: there is no "login" route.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Uploads bigger than the server's post_max_size are rejected before the session
        // starts, so a flashed error would be lost. Return to the form with a flag in the
        // address instead; the form explains it (see reports/form.blade.php).
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The files are too big for the server (limit ' . ini_get('post_max_size') . ').'], 413);
            }

            $back = parse_url(url()->previous(), PHP_URL_PATH) ?: '/submit';

            return redirect($back . '?upload_error=too_large');
        });
    })->create();
