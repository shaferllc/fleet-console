<?php

namespace Tests\Feature;

use App\Models\FleetConsoleSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetConsoleOperationalSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsConsole(): self
    {
        return $this->withSession(['fleet_console_ok' => true]);
    }

    public function test_guest_redirects_from_operational_settings(): void
    {
        $this->get(route('console.settings.operational'))
            ->assertRedirect(route('console.login'));
    }

    public function test_authenticated_user_can_update_operational_settings(): void
    {
        $this->actingAsConsole()
            ->put(route('console.settings.operational.update'), [
                'http_verify' => '0',
                'daily_rollup_sparkline_after_samples' => '600',
                'api_token' => 'api-secret-token-32chars-minimum-here',
                'trusted_ips' => '127.0.0.1',
                'health_token' => '',
                'background_poll_enabled' => '1',
                'poll_interval_minutes' => '15',
                'clear_api_token' => '0',
                'clear_health_token' => '0',
            ])
            ->assertRedirect(route('console.settings.operational'));

        $row = FleetConsoleSetting::query()->first();
        $this->assertNotNull($row);
        $this->assertFalse($row->http_verify);
        $this->assertSame(600, (int) $row->daily_rollup_sparkline_after_samples);
        $this->assertSame('127.0.0.1', $row->trusted_ips);
        $this->assertTrue($row->background_poll_enabled);
        $this->assertSame(15, (int) $row->poll_interval_minutes);
    }
}
