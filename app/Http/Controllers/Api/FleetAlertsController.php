<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FleetAlertEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FleetAlertsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 40);
        $limit = max(1, min(100, $limit));

        $since = null;
        $rawSince = $request->query('since');
        if (is_string($rawSince) && $rawSince !== '') {
            try {
                $since = Carbon::parse($rawSince);
            } catch (\Throwable) {
                return response()->json(['message' => 'Invalid since (use ISO 8601).'], 422);
            }
        }

        $targetKey = $this->optionalQueryString($request, 'target_key', 64);
        if ($targetKey === false) {
            return response()->json(['message' => 'Invalid target_key (max 64 characters).'], 422);
        }

        $type = $this->optionalQueryString($request, 'type', 48);
        if ($type === false) {
            return response()->json(['message' => 'Invalid type (max 48 characters).'], 422);
        }

        $q = FleetAlertEvent::query()->orderByDesc('id');
        if ($since !== null) {
            $q->where('created_at', '>=', $since);
        }
        if ($targetKey !== null) {
            $q->where('target_key', $targetKey);
        }
        if ($type !== null) {
            $q->where('type', $type);
        }

        $rows = $q->limit($limit)->get();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'filters' => [
                'limit' => $limit,
                'since' => $since?->toIso8601String(),
                'target_key' => $targetKey,
                'type' => $type,
            ],
            'events' => $rows->map(static function (FleetAlertEvent $e): array {
                return [
                    'id' => $e->id,
                    'target_key' => $e->target_key,
                    'type' => $e->type,
                    'subject' => $e->subject,
                    'body' => $e->body,
                    'channels' => $e->channels,
                    'created_at' => $e->created_at?->toIso8601String(),
                ];
            })->values()->all(),
        ]);
    }

    /**
     * @return string|null|false null = omit filter; false = validation error
     */
    private function optionalQueryString(Request $request, string $name, int $maxLen): string|null|false
    {
        $raw = $request->query($name);
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        $value = trim($raw);
        if ($value === '') {
            return null;
        }
        if (strlen($value) > $maxLen) {
            return false;
        }

        return $value;
    }
}
