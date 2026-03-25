<?php

namespace Tests\Feature;

use App\Models\FleetTarget;
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

    public function test_can_create_target_and_it_overrides_file_default_targets(): void
    {
        $this->actingAsConsole()
            ->post(route('console.targets.store'), [
                'key' => 'alpha',
                'name' => 'Alpha',
                'description' => 'Test',
                'base_url' => 'https://alpha.test',
                'site_url' => '',
                'operator_path_prefix' => '/api/operator',
                'operator_token' => '',
                'sort_order' => 0,
                'is_enabled' => '1',
            ])
            ->assertRedirect(route('console.targets.index'));

        $this->assertDatabaseHas('fleet_targets', ['key' => 'alpha', 'name' => 'Alpha']);

        $this->actingAsConsole()
            ->get(route('console.dashboard'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Drift');
    }

    public function test_import_skips_existing_keys_and_adds_the_rest(): void
    {
        FleetTarget::query()->create([
            'key' => 'beacon',
            'name' => 'Beacon Custom',
            'description' => null,
            'base_url' => 'https://custom-beacon.test',
            'site_url' => null,
            'operator_path_prefix' => '/api/operator',
            'operator_token' => null,
            'sort_order' => 0,
            'is_enabled' => true,
        ]);

        $before = FleetTarget::query()->count();

        $this->actingAsConsole()
            ->post(route('console.targets.import'))
            ->assertRedirect(route('console.targets.index'));

        $after = FleetTarget::query()->count();
        $this->assertGreaterThan($before, $after);
        $this->assertSame('Beacon Custom', FleetTarget::query()->where('key', 'beacon')->value('name'));
    }
}
