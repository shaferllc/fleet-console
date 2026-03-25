<?php

use App\Http\Controllers\ConsoleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FleetRefreshController;
use App\Http\Controllers\FleetTargetDetailController;
use App\Http\Controllers\ProjectReadmeController;
use Illuminate\Support\Facades\Route;

Route::middleware('fleet.trusted_ip')->group(function (): void {
    Route::get('/login', [ConsoleAuthController::class, 'showLogin'])->name('console.login');
    Route::post('/login', [ConsoleAuthController::class, 'login']);

    Route::middleware('fleet.console')->group(function (): void {
        Route::get('/', DashboardController::class)->name('console.dashboard');
        Route::post('/refresh', [FleetRefreshController::class, 'refreshAll'])->name('console.refresh.all');
        Route::post('/refresh/{key}', [FleetRefreshController::class, 'refreshOne'])
            ->where('key', '[a-z0-9-]+')
            ->name('console.refresh.one');
        Route::get('/targets/{key}/poll-detail', [FleetTargetDetailController::class, 'show'])
            ->where('key', '[a-z0-9-]+')
            ->name('console.targets.poll-detail');
        Route::get('/project/{key}', [ProjectReadmeController::class, 'show'])
            ->where('key', '[a-z0-9-]+')
            ->name('console.project.readme');
        Route::post('/logout', [ConsoleAuthController::class, 'logout'])->name('console.logout');
    });
});
