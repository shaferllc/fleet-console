<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetOpenApiJsonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fleetSettings()->update(['api_token' => 'secret']);
    }

    public function test_openapi_json_requires_api_token(): void
    {
        $this->getJson('/api/fleet/openapi.json')
            ->assertUnauthorized();
    }

    public function test_openapi_json_returns_document(): void
    {
        $response = $this->getJson('/api/fleet/openapi.json', ['Authorization' => 'Bearer secret'])
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.3');

        $paths = $response->json('paths');
        $this->assertIsArray($paths);
        $this->assertSame('Configured targets (no secrets)', $paths['/api/fleet/targets']['get']['summary']);
        $this->assertStringContainsString('poll history', $paths['/api/fleet/targets/{key}']['get']['summary']);
        $this->assertSame('JSON fleet snapshot', $paths['/api/fleet/summary']['get']['summary']);
        $this->assertSame('Recent alert dispatch audit log (newest first)', $paths['/api/fleet/alerts']['get']['summary']);
        $this->assertSame('Prometheus exposition format', $paths['/api/fleet/metrics']['get']['summary']);
        $this->assertStringContainsString('DB liveness', $paths['/api/fleet/health']['get']['summary']);
    }
}
