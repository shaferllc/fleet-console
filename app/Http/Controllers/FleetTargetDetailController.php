<?php

namespace App\Http\Controllers;

use App\Services\FleetPollHistory;
use Illuminate\Http\JsonResponse;

class FleetTargetDetailController extends Controller
{
    public function show(string $key, FleetPollHistory $history): JsonResponse
    {
        $payload = $history->targetDetailPayload($key);
        if ($payload === null) {
            return response()->json(['message' => 'Unknown target.'], 404);
        }

        return response()->json($payload);
    }
}
