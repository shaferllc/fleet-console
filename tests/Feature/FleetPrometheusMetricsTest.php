<?php

namespace Tests\Feature;

use App\Models\FleetAlertEvent;
use App\Models\FleetPollSample;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetPrometheusMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_returns_prometheus_text(): void
    {
        $this->fleetSettings()->update(['api_token' => 'tok']);
        $this->installFleetTarget([
            'key' => 'alpha',
            'name' => 'Alpha',
            'base_url' => 'https://alpha.test',
        ]);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 50,
            'error_message' => null,
            'summary_snapshot' => null,
            'created_at' => now(),
        ]);

        FleetAlertEvent::query()->create([
            'target_key' => 'alpha',
            'type' => 'down',
            'subject' => 's',
            'body' => 'b',
            'channels' => 'slack',
            'created_at' => now()->subHour(),
        ]);

        $this->get('/api/fleet/metrics', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8; version=0.0.4');

        $body = $this->get('/api/fleet/metrics', ['Authorization' => 'Bearer tok'])->getContent();
        $this->assertStringContainsString('fleet_target_up{target="alpha"} 1', $body);
        $this->assertStringContainsString('# TYPE fleet_target_up gauge', $body);
        $this->assertStringContainsString('fleet_alert_events_24h 1', $body);
        $this->assertStringContainsString('# TYPE fleet_alert_events_24h gauge', $body);
    }
}
