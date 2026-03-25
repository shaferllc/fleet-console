<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FleetSummaryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FleetSummaryController extends Controller
{
    public function __invoke(Request $request, FleetSummaryBuilder $builder): JsonResponse
    {
        $since = null;
        $rawSince = $request->query('since');
        if (is_string($rawSince) && $rawSince !== '') {
            try {
                $since = Carbon::parse($rawSince);
            } catch (\Throwable) {
                return response()->json(['message' => 'Invalid since (use ISO 8601).'], 422);
            }
        }

        $onlyKeys = null;
        $rawKeys = $request->query('keys');
        if (is_string($rawKeys) && $rawKeys !== '') {
            $onlyKeys = array_values(array_filter(array_map('trim', explode(',', $rawKeys))));
            if ($onlyKeys === []) {
                $onlyKeys = null;
            }
        }

        return response()->json($builder->build($since, $onlyKeys));
    }
}
