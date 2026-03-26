<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetHealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_returns_ok_without_token_when_not_configured(): void
    {
        $this->fleetSettings()->update(['health_token' => null]);

        $this->getJson('/api/fleet/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('database', true);
    }

    public function test_health_requires_token_when_configured(): void
    {
        $this->fleetSettings()->update(['health_token' => 'secret-health']);

        $this->getJson('/api/fleet/health')
            ->assertForbidden()
            ->assertJsonPath('status', 'forbidden');

        $this->getJson('/api/fleet/health?token=secret-health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->getJson('/api/fleet/health', ['X-Fleet-Health-Token' => 'secret-health'])
            ->assertOk();
    }
}
