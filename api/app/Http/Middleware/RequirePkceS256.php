<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The authorization step accepts only PKCE with S256. league/oauth2-server always registers the `plain` method
 * (and treats a missing method as `plain`), which would let a client send the verifier itself as the challenge.
 * Refused here with a plain 400 and no redirect, so nothing is ever sent to the redirect_uri.
 */
class RequirePkceS256
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && $request->is('oauth/authorize') && $request->query('code_challenge_method') !== 'S256') {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'PKCE with code_challenge_method=S256 is required.',
            ], 400);
        }

        return $next($request);
    }
}
