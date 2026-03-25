<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFleetApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('fleet_console.api_token');
        if (! is_string($expected) || $expected === '') {
            return response()->json([
                'message' => 'Fleet read API is not configured (set FLEET_CONSOLE_API_TOKEN).',
            ], 503);
        }

        $provided = $request->bearerToken();
        if ($provided === null && $request->hasHeader('X-Fleet-Api-Token')) {
            $provided = $request->header('X-Fleet-Api-Token');
        }

        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Invalid API token.'], 401);
        }

        return $next($request);
    }
}
