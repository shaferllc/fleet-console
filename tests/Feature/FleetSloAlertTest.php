<?php

namespace Tests\Feature;

use App\Services\FleetAlertDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FleetSloAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_slo_breach_posts_slack_once_and_dedupes_via_cache(): void
    {
        Http::fake();
        Cache::flush();

        config([
            'fleet_console.alert_email' => '',
            'fleet_console.alert_slack_webhook' => 'https://hooks.example.test/fleet-slo',
            'fleet_console.alert_slo_min_ok_percent' => 99.0,
            'fleet_console.alert_slo_dedupe_hours' => 6,
        ]);

        $dispatcher = app(FleetAlertDispatcher::class);
        $dispatcher->maybeSloBreach('beacon', 'Beacon', 95.5);
        $dispatcher->maybeSloBreach('beacon', 'Beacon', 95.5);

        Http::assertSentCount(1);
        $this->assertTrue(Cache::has('fleet:slo:breach:'.sha1('beacon|'.now()->format('Y-m-d'))));
    }

    public function test_slo_not_sent_when_above_threshold(): void
    {
        Http::fake();
        Cache::flush();

        config([
            'fleet_console.alert_email' => '',
            'fleet_console.alert_slack_webhook' => 'https://hooks.example.test/fleet-slo',
            'fleet_console.alert_slo_min_ok_percent' => 99.0,
        ]);

        $dispatcher = app(FleetAlertDispatcher::class);
        $dispatcher->maybeSloBreach('beacon', 'Beacon', 99.9);

        Http::assertSentCount(0);
    }
}
