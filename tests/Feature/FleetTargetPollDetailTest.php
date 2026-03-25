<?php

namespace Tests\Feature;

use App\Models\FleetPollSample;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetTargetPollDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_poll_detail_redirects_when_not_authenticated(): void
    {
        $this->get('/targets/demo/poll-detail')->assertRedirect(route('console.login'));
    }

    public function test_poll_detail_returns_404_for_unknown_target(): void
    {
        config(['fleet_console.targets' => []]);

        $this->withSession(['fleet_console_ok' => true])
            ->getJson('/targets/unknown/poll-detail')
            ->assertNotFound();
    }

    public function test_poll_detail_returns_expected_json_shape(): void
    {
        config(['fleet_console.targets' => [
            ['key' => 'alpha', 'name' => 'Alpha App'],
        ]]);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 42,
            'error_message' => null,
            'summary_snapshot' => ['users' => 1],
            'created_at' => now(),
        ]);

        $this->withSession(['fleet_console_ok' => true])
            ->getJson('/targets/alpha/poll-detail')
            ->assertOk()
            ->assertJsonPath('key', 'alpha')
            ->assertJsonPath('name', 'Alpha App')
            ->assertJsonStructure([
                'last_ok_summary',
                'recent_errors',
                'recent_polls',
                'slo_24h',
                'slo_7d',
                'sparkline_24h',
                'sparkline_7d',
                'sparkline_7d_rollups',
            ]);
    }
}
