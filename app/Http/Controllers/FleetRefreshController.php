<?php

namespace App\Http\Controllers;

use App\Models\FleetAlertEvent;
use App\Services\FleetPollHistory;
use App\Services\FleetTargetPoller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetRefreshController extends Controller
{
    public function refreshAll(FleetTargetPoller $poller, FleetPollHistory $history): JsonResponse
    {
        $results = $poller->pollAll();
        $results = $history->attachLastPollAtToRows($results);
        $total = count($results);
        $okCount = collect($results)->where('ok', true)->count();
        $vis = $history->fleetVisibilitySnapshot();

        return response()->json([
            'refreshed_at' => now()->toIso8601String(),
            'counts' => [
                'total' => $total,
                'ok' => $okCount,
                'err' => $total - $okCount,
            ],
            'html_stats' => view('console.partials.fleet-stats-strip', [
                'total' => $total,
                'okCount' => $okCount,
                'errCount' => $total - $okCount,
                ...$vis,
            ])->render(),
            'html_alerts' => $this->renderAlertTimelineHtml(),
            'html_grid' => view('console.partials.fleet-cards-grid', [
                'results' => $results,
            ])->render(),
            'html_compare' => $total > 0
                ? view('console.partials.fleet-compare-section', [
                    'rows' => $results,
                ])->render()
                : '',
        ]);
    }

    public function refreshOne(Request $request, string $key, FleetTargetPoller $poller, FleetPollHistory $history): JsonResponse
    {
        $row = $poller->pollTargetByKey($key);
        if ($row === null) {
            return response()->json(['message' => 'Unknown target.'], 404);
        }

        $row = $history->attachLastPollAtToRows([$row])[0];
        $counts = $history->terminalStatusCounts();
        $vis = $history->fleetVisibilitySnapshot();

        return response()->json([
            'refreshed_at' => now()->toIso8601String(),
            'key' => $key,
            'html' => view('console.partials.fleet-target-card', [
                'row' => $row,
            ])->render(),
            'html_stats' => view('console.partials.fleet-stats-strip', [
                'total' => $counts['total'],
                'okCount' => $counts['ok'],
                'errCount' => $counts['err'],
                ...$vis,
            ])->render(),
            'html_alerts' => $this->renderAlertTimelineHtml(),
            'html_compare_row' => view('console.partials.fleet-compare-row', [
                'row' => $row,
            ])->render(),
        ]);
    }

    private function renderAlertTimelineHtml(): string
    {
        $alertEvents = FleetAlertEvent::query()
            ->orderByDesc('id')
            ->limit(40)
            ->get();

        return view('console.partials.fleet-alert-timeline', [
            'alertEvents' => $alertEvents,
        ])->render();
    }
}
