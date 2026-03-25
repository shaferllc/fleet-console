<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PollFleetTargetsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_skips_when_background_poll_disabled(): void
    {
        config(['fleet_console.background_poll_enabled' => false]);

        $this->artisan('fleet:poll-targets')
            ->assertSuccessful();
    }

    public function test_polls_when_enabled(): void
    {
        Http::fake([
            'https://alpha.test/api/operator/summary' => Http::response(['ok' => true], 200),
        ]);

        config([
            'fleet_console.background_poll_enabled' => true,
            'fleet_console.http_verify' => false,
            'fleet_console.targets' => [
                ['key' => 'alpha', 'name' => 'Alpha', 'base_url' => 'https://alpha.test', 'operator_token' => 'op-token'],
            ],
        ]);

        $this->artisan('fleet:poll-targets')
            ->assertSuccessful();

        $this->assertDatabaseHas('fleet_poll_samples', [
            'target_key' => 'alpha',
            'ok' => 1,
        ]);
    }
}
