<?php

namespace Tests\Feature;

use App\Models\FleetTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderFleetTargetsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsConsole(): self
    {
        return $this->withSession(['fleet_console_ok' => true]);
    }

    public function test_guest_cannot_reorder_targets(): void
    {
        $this->postJson(route('console.targets.reorder'), [
            'order' => ['a', 'b'],
        ])->assertRedirect(route('console.login'));
    }

    public function test_reorder_updates_sort_order(): void
    {
        FleetTarget::query()->create([
            'key' => 'zebra',
            'name' => 'Zebra',
            'description' => null,
            'base_url' => 'https://zebra.test',
            'site_url' => null,
            'operator_path_prefix' => '/api/operator',
            'operator_token' => 'zebra-op-token-32chars-minimum',
            'sort_order' => 0,
            'is_enabled' => true,
        ]);
        FleetTarget::query()->create([
            'key' => 'alpha',
            'name' => 'Alpha',
            'description' => null,
            'base_url' => 'https://alpha.test',
            'site_url' => null,
            'operator_path_prefix' => '/api/operator',
            'operator_token' => 'alpha-op-token-32chars-minimum',
            'sort_order' => 10,
            'is_enabled' => true,
        ]);

        $this->actingAsConsole()
            ->postJson(route('console.targets.reorder'), [
                'order' => ['alpha', 'zebra'],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(0, FleetTarget::query()->where('key', 'alpha')->value('sort_order'));
        $this->assertSame(10, FleetTarget::query()->where('key', 'zebra')->value('sort_order'));
    }
}
