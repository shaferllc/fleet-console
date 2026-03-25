<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FleetHealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $expected = config('fleet_console.health_token');
        if (is_string($expected) && $expected !== '') {
            $q = $request->query('token');
            $h = $request->header('X-Fleet-Health-Token');
            if ((! is_string($q) || ! hash_equals($expected, $q))
                && (! is_string($h) || ! hash_equals($expected, $h))) {
                return response()->json(['status' => 'forbidden'], 403);
            }
        }

        try {
            DB::select('select 1 as ok');

            return response()->json([
                'status' => 'ok',
                'database' => true,
            ]);
        } catch (\Throwable) {
            return response()->json([
                'status' => 'degraded',
                'database' => false,
            ]);
        }
    }
}
