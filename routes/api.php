<?php

use App\Http\Controllers\Api\FleetAlertsController;
use App\Http\Controllers\Api\FleetHealthController;
use App\Http\Controllers\Api\FleetOpenApiController;
use App\Http\Controllers\Api\FleetPrometheusMetricsController;
use App\Http\Controllers\Api\FleetSummaryController;
use App\Http\Controllers\Api\FleetTargetsIndexController;
use App\Http\Controllers\Api\FleetTargetShowController;
use Illuminate\Support\Facades\Route;

Route::get('/fleet/health', FleetHealthController::class)->name('api.fleet.health');

Route::middleware(['fleet.trusted_ip', 'fleet.api', 'throttle:120,1'])->group(function (): void {
    Route::get('/fleet/targets', FleetTargetsIndexController::class)->name('api.fleet.targets.index');
    Route::get('/fleet/targets/{key}', FleetTargetShowController::class)
        ->where('key', '[a-z0-9-]+')
        ->name('api.fleet.targets.show');
    Route::get('/fleet/summary', FleetSummaryController::class)->name('api.fleet.summary');
    Route::get('/fleet/alerts', FleetAlertsController::class)->name('api.fleet.alerts');
    Route::get('/fleet/metrics', FleetPrometheusMetricsController::class)->name('api.fleet.metrics');
    Route::get('/fleet/openapi.json', FleetOpenApiController::class)->name('api.fleet.openapi');
});
