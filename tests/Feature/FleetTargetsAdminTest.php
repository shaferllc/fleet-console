<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetTargetsAdminTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsConsole(): self
    {
        return $this->withSession(['fleet_console_ok' => true]);
    }

    public function test_guest_redirects_from_targets_index(): void
    {
        $this->get(route('console.targets.index'))
            ->assertRedirect(route('console.login'));
    }

    public function test_authenticated_user_can_view_targets_index(): void
    {
        $this->actingAsConsole()
            ->get(route('console.targets.index'))
            ->assertOk()
            ->assertSee('Services');
    }

    public function test_can_create_target_and_it_appears_on_dashboard(): void
    {
        $this->actingAsConsole()
            ->post(route('console.targets.store'), [
                'key' => 'alpha',
                'name' => 'Alpha',
                'description' => 'Test',
                'base_url' => 'https://alpha.test',
                'site_url' => '',
                'staging_site_url' => 'https://staging.alpha.test',
                'operator_path_prefix' => '/api/operator',
                'operator_token' => 'alpha-op-token-32chars-min',
                'sort_order' => 0,
                'is_enabled' => '1',
            ])
            ->assertRedirect(route('console.targets.index'));

        $this->assertDatabaseHas('fleet_targets', [
            'key' => 'alpha',
            'name' => 'Alpha',
            'staging_site_url' => 'https://staging.alpha.test',
        ]);

        $this->actingAsConsole()
            ->get(route('console.dashboard'))
            ->assertOk()
            ->assertSee('Alpha');
    }
}
