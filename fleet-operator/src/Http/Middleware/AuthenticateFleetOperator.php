<?php

namespace Fleetphp\FleetOperator\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFleetOperator
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('fleet_operator.token');

        if (! is_string($expected) || $expected === '') {
            return response()->json([
                'message' => 'Operator API is not configured (FLEET_OPERATOR_TOKEN).',
            ], 404);
        }

        $provided = $request->bearerToken();
        if ($provided === null || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
