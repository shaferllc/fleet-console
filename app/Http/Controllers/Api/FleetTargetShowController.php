<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FleetPollHistory;
use Illuminate\Http\JsonResponse;

class FleetTargetShowController extends Controller
{
    public function __invoke(string $key, FleetPollHistory $history): JsonResponse
    {
        $payload = $history->targetDetailPayload($key);
        if ($payload === null) {
            return response()->json(['message' => 'Unknown target.'], 404);
        }

        return response()->json($payload);
    }
}
