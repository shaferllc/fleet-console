<?php

namespace Tests\Feature;

use App\Models\FleetPollSample;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetApiSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_404_when_api_token_not_configured(): void
    {
        config(['fleet_console.api_token' => '']);

        $this->getJson('/api/fleet/summary', ['Authorization' => 'Bearer anything'])
            ->assertNotFound()
            ->assertJsonFragment(['message' => 'Fleet read API is not configured (set FLEET_CONSOLE_API_TOKEN).']);
    }

    public function test_summary_returns_401_for_invalid_token(): void
    {
        config(['fleet_console.api_token' => 'secret-token']);

        $this->getJson('/api/fleet/summary', ['Authorization' => 'Bearer wrong'])
            ->assertUnauthorized()
            ->assertJsonFragment(['message' => 'Invalid API token.']);
    }

    public function test_summary_accepts_x_fleet_api_token_header(): void
    {
        config([
            'fleet_console.api_token' => 'good-token',
            'fleet_console.targets' => [
                ['key' => 'alpha', 'name' => 'Alpha', 'base_url' => 'https://alpha.test'],
            ],
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

        $this->getJson('/api/fleet/summary', ['X-Fleet-Api-Token' => 'good-token'])
            ->assertOk()
            ->assertJsonPath('source', 'stored_polls')
            ->assertJsonPath('fleet.targets_total', 1)
            ->assertJsonPath('targets.0.key', 'alpha')
            ->assertJsonPath('filters.since', null)
            ->assertJsonPath('filters.keys', null)
            ->assertJsonStructure([
                'generated_at',
                'source',
                'filters' => [
                    'since',
                    'keys',
                ],
                'fleet' => [
                    'targets_total',
                    'targets_ok_latest_poll',
                    'targets_err_latest_poll',
                    'polls_24h',
                    'polls_7d',
                    'ok_percent_24h',
                    'ok_percent_7d',
                ],
                'targets' => [
                    [
                        'key',
                        'name',
                        'latest_poll',
                        'slo',
                        'latency',
                        'urls',
                    ],
                ],
            ]);
    }

    public function test_summary_invalid_since_returns_422(): void
    {
        config(['fleet_console.api_token' => 't']);

        $this->getJson('/api/fleet/summary?since=not-a-date', ['Authorization' => 'Bearer t'])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Invalid since (use ISO 8601).']);
    }

    public function test_summary_since_excludes_targets_whose_latest_poll_is_older(): void
    {
        config([
            'fleet_console.api_token' => 't',
            'fleet_console.targets' => [
                ['key' => 'stale', 'name' => 'Stale', 'base_url' => 'https://stale.test'],
                ['key' => 'fresh', 'name' => 'Fresh', 'base_url' => 'https://fresh.test'],
            ],
        ]);

        FleetPollSample::query()->create([
            'target_key' => 'stale',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 10,
            'error_message' => null,
            'summary_snapshot' => null,
            'created_at' => now()->subDays(3),
        ]);
        FleetPollSample::query()->create([
            'target_key' => 'fresh',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 10,
            'error_message' => null,
            'summary_snapshot' => null,
            'created_at' => now()->subHour(),
        ]);

        $since = now()->subDay()->toIso8601String();

        $this->getJson('/api/fleet/summary?since='.urlencode($since), ['Authorization' => 'Bearer t'])
            ->assertOk()
            ->assertJsonPath('filters.since', $since)
            ->assertJsonCount(1, 'targets')
            ->assertJsonPath('targets.0.key', 'fresh');
    }

    public function test_summary_keys_parameter_filters_targets(): void
    {
        config([
            'fleet_console.api_token' => 't',
            'fleet_console.targets' => [
                ['key' => 'alpha', 'name' => 'Alpha', 'base_url' => 'https://alpha.test'],
                ['key' => 'beta', 'name' => 'Beta', 'base_url' => 'https://beta.test'],
            ],
        ]);

        foreach (['alpha', 'beta'] as $key) {
            FleetPollSample::query()->create([
                'target_key' => $key,
                'ok' => true,
                'http_status' => 200,
                'latency_ms' => 10,
                'error_message' => null,
                'summary_snapshot' => null,
                'created_at' => now(),
            ]);
        }

        $this->getJson('/api/fleet/summary?keys=beta', ['Authorization' => 'Bearer t'])
            ->assertOk()
            ->assertJsonPath('filters.keys', ['beta'])
            ->assertJsonPath('targets.0.key', 'beta')
            ->assertJsonCount(1, 'targets');
    }
}
