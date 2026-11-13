<?php

use App\Exceptions\JsonErrorRenderer;
use App\Http\Middleware\RequirePkceS256;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Passport registers /oauth/authorize in the web group; PKCE must be S256 (see the middleware).
        $middleware->web(prepend: [RequirePkceS256::class]);

        // /api/* guests get the JSON 401. Browser pages (the OAuth authorize step) go to the login page.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // JSON for the API; browser navigations (login, authorize) keep Laravel's redirects.
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*', 'oauth/token') || $request->expectsJson());
        $exceptions->render(function (Throwable $e, Request $request) {
            $browser = ! $request->is('api/*') && ! $request->expectsJson();

            // Browser pages (login form, authorize step) keep Laravel's redirects and flashed errors.
            if ($browser && ($e instanceof AuthenticationException || $e instanceof ValidationException)) {
                return null;
            }

            return (new JsonErrorRenderer)($e, $request);
        });
    })->create();
