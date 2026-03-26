<?php

namespace Tests\Feature;

use App\Models\FleetPollSample;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetTargetsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fleetSettings()->update(['api_token' => 'tok']);
    }

    public function test_index_lists_targets_without_operator_token(): void
    {
        $this->installFleetTarget([
            'key' => 'alpha',
            'name' => 'Alpha',
            'base_url' => 'https://alpha.test',
            'operator_token' => 'super-secret',
            'description' => 'App one',
        ]);

        $this->getJson('/api/fleet/targets', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonCount(1, 'targets')
            ->assertJsonPath('targets.0.key', 'alpha')
            ->assertJsonPath('targets.0.name', 'Alpha')
            ->assertJsonPath('targets.0.staging_site_url', null)
            ->assertJsonPath('targets.0.urls.staging_site', null)
            ->assertJsonPath('targets.0.urls.operator_summary', 'https://alpha.test/api/operator/summary')
            ->assertJsonMissingPath('targets.0.operator_token');
    }

    public function test_index_includes_staging_site_when_configured(): void
    {
        $this->installFleetTarget([
            'key' => 'alpha',
            'name' => 'Alpha',
            'base_url' => 'https://alpha.test',
            'staging_site_url' => 'https://staging.alpha.test',
        ]);

        $this->getJson('/api/fleet/targets', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonPath('targets.0.staging_site_url', 'https://staging.alpha.test')
            ->assertJsonPath('targets.0.urls.staging_site', 'https://staging.alpha.test');
    }

    public function test_show_returns_same_shape_as_poll_detail_for_known_target(): void
    {
        $this->installFleetTarget([
            'key' => 'alpha',
            'name' => 'Alpha',
            'base_url' => 'https://alpha.test',
        ]);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 10,
            'error_message' => null,
            'summary_snapshot' => ['users' => 2],
            'created_at' => now(),
        ]);

        $this->getJson('/api/fleet/targets/alpha', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonStructure([
                'key',
                'name',
                'last_ok_summary',
                'recent_errors',
                'recent_polls',
                'slo_24h',
                'slo_7d',
                'sparkline_24h',
                'sparkline_7d',
                'sparkline_7d_rollups',
            ])
            ->assertJsonPath('key', 'alpha')
            ->assertJsonPath('last_ok_summary.users', 2);
    }

    public function test_show_404_for_unknown_target(): void
    {
        $this->getJson('/api/fleet/targets/unknown', ['Authorization' => 'Bearer tok'])
            ->assertNotFound();
    }
}
