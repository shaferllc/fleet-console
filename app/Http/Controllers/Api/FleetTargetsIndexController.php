<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\FleetTargetPublicInfo;
use Illuminate\Http\JsonResponse;

class FleetTargetsIndexController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $targets = [];
        foreach (config('fleet_console.targets', []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $entry = FleetTargetPublicInfo::apiListRow($row);
            if ($entry !== null) {
                $targets[] = $entry;
            }
        }

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'targets' => $targets,
        ]);
    }
}
