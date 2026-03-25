<?php

namespace Tests\Feature;

use App\Models\FleetPollSample;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FleetRefreshOneSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_one_returns_card_stats_strip_and_compare_row_html(): void
    {
        config([
            'fleet_console.targets' => [
                ['key' => 'alpha', 'name' => 'Alpha', 'base_url' => 'https://alpha.test', 'operator_token' => 'tok'],
            ],
            'fleet_console.http_verify' => false,
        ]);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 30,
            'error_message' => null,
            'summary_snapshot' => null,
            'created_at' => now()->subMinutes(10),
        ]);

        Http::fake([
            'https://alpha.test/api/operator/summary' => Http::response(['users' => 2], 200),
        ]);

        $response = $this->withSession(['fleet_console_ok' => true])
            ->postJson(route('console.refresh.one', ['key' => 'alpha']));

        $response->assertOk()
            ->assertJsonStructure(['html', 'html_stats', 'html_alerts', 'html_compare_row', 'key']);

        $htmlStats = $response->json('html_stats');
        $htmlRow = $response->json('html_compare_row');
        $htmlAlerts = $response->json('html_alerts');
        $this->assertStringContainsString('fleet-stats-strip', $htmlStats);
        $this->assertStringContainsString('fleet-alert-timeline-section', $htmlAlerts);
        $this->assertStringContainsString('fc-compare-row', $htmlRow);
        $this->assertStringContainsString('data-compare-key="alpha"', $htmlRow);
    }
}
