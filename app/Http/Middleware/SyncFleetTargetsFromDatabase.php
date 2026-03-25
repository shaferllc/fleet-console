<?php

namespace App\Http\Middleware;

use App\Support\FleetConsoleDynamicConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncFleetTargetsFromDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        FleetConsoleDynamicConfig::syncTargetsFromDatabase();

        return $next($request);
    }
}
