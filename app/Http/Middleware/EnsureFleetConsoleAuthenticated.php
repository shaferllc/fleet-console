<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFleetConsoleAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('fleet_console_ok')) {
            return redirect()->guest(route('console.login'));
        }

        return $next($request);
    }
}
