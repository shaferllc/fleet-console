<?php

namespace Tests\Feature;

use App\Models\FleetConsoleSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetConsoleSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsConsole(): self
    {
        return $this->withSession(['fleet_console_ok' => true]);
    }

    public function test_guest_redirects_from_alert_settings(): void
    {
        $this->get(route('console.settings.alerts'))
            ->assertRedirect(route('console.login'));
    }

    public function test_authenticated_user_can_view_alert_settings(): void
    {
        $this->actingAsConsole()
            ->get(route('console.settings.alerts'))
            ->assertOk()
            ->assertSee('Alert settings', false);
    }

    public function test_authenticated_user_can_update_alert_settings(): void
    {
        $this->actingAsConsole()
            ->put(route('console.settings.alerts.update'), [
                'alert_email' => 'ops@example.com',
                'alert_slack_webhook' => '',
                'alert_on_recovery' => '1',
                'alert_slo_min_ok_percent' => '99',
                'alert_slo_dedupe_hours' => '12',
                'alert_metric_rules_json' => '{"*":[{"path":"users","min":1}]}',
                'alert_webhook_urls_json' => '["https://hooks.example.test/a"]',
            ])
            ->assertRedirect(route('console.settings.alerts'));

        $row = FleetConsoleSetting::query()->first();
        $this->assertNotNull($row);
        $this->assertSame('ops@example.com', $row->alert_email);
        $this->assertTrue($row->alert_on_recovery);
        $this->assertSame(99.0, (float) $row->alert_slo_min_ok_percent);
        $this->assertSame(12, (int) $row->alert_slo_dedupe_hours);
        $this->assertArrayHasKey('*', $row->alert_metric_rules ?? []);
        $this->assertSame(['https://hooks.example.test/a'], $row->alert_webhook_urls ?? []);
    }
}
