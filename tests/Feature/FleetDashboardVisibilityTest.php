<?php

namespace Tests\Feature;

use App\Models\FleetPollSample;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetDashboardVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_fleet_comparison_for_authenticated_user(): void
    {
        config([
            'fleet_console.targets' => [
                ['key' => 'alpha', 'name' => 'Alpha', 'base_url' => 'https://alpha.test', 'operator_token' => 't'],
            ],
            'fleet_console.operator_token' => 't',
            'fleet_console.http_verify' => false,
        ]);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 40,
            'error_message' => null,
            'summary_snapshot' => ['users' => 1],
            'created_at' => now()->subHour(),
        ]);

        $this->withSession(['fleet_console_ok' => true])
            ->get(route('console.dashboard'))
            ->assertOk()
            ->assertSee('Fleet comparison', false)
            ->assertSee('Fleet polls', false)
            ->assertSee('Alert timeline', false);
    }
}
