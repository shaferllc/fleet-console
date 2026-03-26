<?php

namespace Tests\Feature;

use App\Models\FleetAlertEvent;
use App\Models\FleetPollSample;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetTrustedIpMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_is_not_blocked_by_trusted_ips(): void
    {
        $this->fleetSettings()->update([
            'health_token' => null,
            'trusted_ips' => '10.0.0.0/8',
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->getJson('/api/fleet/health')
            ->assertOk();
    }

    public function test_summary_blocked_when_ip_not_allowlisted(): void
    {
        $this->fleetSettings()->update([
            'api_token' => 'tok',
            'trusted_ips' => '10.0.0.0/8',
        ]);
        $this->installFleetTarget([
            'key' => 'alpha',
            'name' => 'Alpha',
            'base_url' => 'https://alpha.test',
        ]);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 1,
            'error_message' => null,
            'summary_snapshot' => null,
            'created_at' => now(),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->getJson('/api/fleet/summary', ['Authorization' => 'Bearer tok'])
            ->assertForbidden();

        $this->withServerVariables(['REMOTE_ADDR' => '10.1.2.3'])
            ->getJson('/api/fleet/summary', ['Authorization' => 'Bearer tok'])
            ->assertOk();
    }

    public function test_alerts_blocked_when_ip_not_allowlisted(): void
    {
        $this->fleetSettings()->update([
            'api_token' => 'tok',
            'trusted_ips' => '10.0.0.0/8',
        ]);

        FleetAlertEvent::query()->create([
            'target_key' => 'a',
            'type' => 'down',
            'subject' => 's',
            'body' => 'b',
            'channels' => null,
            'created_at' => now(),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->getJson('/api/fleet/alerts', ['Authorization' => 'Bearer tok'])
            ->assertForbidden();

        $this->withServerVariables(['REMOTE_ADDR' => '10.5.5.5'])
            ->getJson('/api/fleet/alerts', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonCount(1, 'events');
    }

    public function test_login_routes_respect_trusted_ips(): void
    {
        $this->fleetSettings()->update([
            'trusted_ips' => '10.0.0.0/8',
            'password_hash' => password_hash('secret-login-test', PASSWORD_BCRYPT),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get(route('console.login'))
            ->assertForbidden();

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post(route('console.login'), [
                'email' => 'ops@example.com',
                'password' => 'wrong',
            ])
            ->assertForbidden();

        $this->withServerVariables(['REMOTE_ADDR' => '10.2.3.4'])
            ->get(route('console.login'))
            ->assertOk();
    }
}
