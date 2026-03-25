<?php

namespace App\Http\Controllers;

use App\Models\FleetAlertEvent;
use App\Services\FleetPollHistory;
use App\Services\FleetTargetPoller;
use App\Support\FleetDashboardTargetMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, FleetTargetPoller $poller, FleetPollHistory $history): View
    {
        $results = $poller->pollAll();
        $results = $history->attachLastPollAtToRows($results);
        $results = FleetDashboardTargetMeta::attach($results);
        $total = count($results);
        $okCount = $total ? collect($results)->where('ok', true)->count() : 0;

        return view('console.dashboard', [
            'results' => $results,
            'total' => $total,
            'okCount' => $okCount,
            'errCount' => $total - $okCount,
            'alertEvents' => FleetAlertEvent::query()
                ->orderByDesc('id')
                ->limit(40)
                ->get(),
            ...$history->fleetVisibilitySnapshot(),
        ]);
    }
}
