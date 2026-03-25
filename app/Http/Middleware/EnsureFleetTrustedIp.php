<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class EnsureFleetTrustedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $raw = config('fleet_console.trusted_ips');
        if (! is_string($raw) || trim($raw) === '') {
            return $next($request);
        }

        $clientIp = $request->ip();
        if (! is_string($clientIp) || $clientIp === '') {
            return response('Forbidden', 403);
        }

        foreach (explode(',', $raw) as $entry) {
            $range = trim($entry);
            if ($range === '') {
                continue;
            }
            if (IpUtils::checkIp($clientIp, $range)) {
                return $next($request);
            }
        }

        if ($request->is('api/*')) {
            return response()->json(['message' => 'Forbidden (IP not allowlisted).'], 403);
        }

        return response('Forbidden', 403);
    }
}
