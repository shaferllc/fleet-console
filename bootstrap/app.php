<?php

use App\Http\Middleware\EnsureFleetApiToken;
use App\Http\Middleware\EnsureFleetConsoleAuthenticated;
use App\Http\Middleware\EnsureFleetTrustedIp;
use App\Http\Middleware\SyncFleetTargetsFromDatabase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('fleet:prune-poll-samples')->dailyAt('03:15');
        $schedule->command('fleet:prune-alert-events')->dailyAt('03:18');
        $schedule->command('fleet:aggregate-poll-daily')->dailyAt('03:22');
        $schedule->command('fleet:poll-targets')->everyMinute()->when(function (): bool {
            if (! config('fleet_console.background_poll_enabled')) {
                return false;
            }
            $m = (int) config('fleet_console.poll_interval_minutes');

            return $m > 0 && (now()->minute % $m === 0);
        });
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SyncFleetTargetsFromDatabase::class,
        ]);
        $middleware->api(append: [
            SyncFleetTargetsFromDatabase::class,
        ]);
        $middleware->alias([
            'fleet.console' => EnsureFleetConsoleAuthenticated::class,
            'fleet.api' => EnsureFleetApiToken::class,
            'fleet.trusted_ip' => EnsureFleetTrustedIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
