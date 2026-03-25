<?php

namespace Tests\Feature;

use App\Models\FleetAlertEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetAlertsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_valid_token(): void
    {
        config(['fleet_console.api_token' => 'tok']);

        $this->getJson('/api/fleet/alerts', ['Authorization' => 'Bearer wrong'])
            ->assertUnauthorized();
    }

    public function test_returns_events_newest_first_with_filters(): void
    {
        config(['fleet_console.api_token' => 'tok']);

        FleetAlertEvent::query()->create([
            'target_key' => 'a',
            'type' => 'down',
            'subject' => 'one',
            'body' => 'b1',
            'channels' => 'slack',
            'created_at' => now()->subHours(2),
        ]);
        FleetAlertEvent::query()->create([
            'target_key' => 'b',
            'type' => 'slo_breach',
            'subject' => 'two',
            'body' => 'b2',
            'channels' => 'slack',
            'created_at' => now()->subHour(),
        ]);

        $this->getJson('/api/fleet/alerts?limit=1', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonPath('filters.limit', 1)
            ->assertJsonPath('filters.since', null)
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.subject', 'two');

        $since = now()->subMinutes(90)->toIso8601String();
        $this->getJson('/api/fleet/alerts?since='.urlencode($since), ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.subject', 'two');
    }

    public function test_invalid_since_returns_422(): void
    {
        config(['fleet_console.api_token' => 'tok']);

        $this->getJson('/api/fleet/alerts?since=bad', ['Authorization' => 'Bearer tok'])
            ->assertStatus(422);
    }

    public function test_target_key_and_type_filters(): void
    {
        config(['fleet_console.api_token' => 'tok']);

        FleetAlertEvent::query()->create([
            'target_key' => 'alpha',
            'type' => 'down',
            'subject' => 'a',
            'body' => 'b',
            'channels' => 'slack',
            'created_at' => now(),
        ]);
        FleetAlertEvent::query()->create([
            'target_key' => 'beta',
            'type' => 'slo_breach',
            'subject' => 'b',
            'body' => 'b',
            'channels' => 'slack',
            'created_at' => now(),
        ]);

        $this->getJson('/api/fleet/alerts?target_key=alpha', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonPath('filters.target_key', 'alpha')
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.type', 'down');

        $this->getJson('/api/fleet/alerts?type=slo_breach', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonPath('filters.type', 'slo_breach')
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.target_key', 'beta');
    }

    public function test_oversized_query_params_return_422(): void
    {
        config(['fleet_console.api_token' => 'tok']);

        $this->getJson('/api/fleet/alerts?target_key='.str_repeat('x', 65), ['Authorization' => 'Bearer tok'])
            ->assertStatus(422);

        $this->getJson('/api/fleet/alerts?type='.str_repeat('y', 49), ['Authorization' => 'Bearer tok'])
            ->assertStatus(422);
    }

    public function test_limit_is_capped_at_100(): void
    {
        config(['fleet_console.api_token' => 'tok']);

        for ($i = 0; $i < 5; $i++) {
            FleetAlertEvent::query()->create([
                'target_key' => 'x',
                'type' => 't',
                'subject' => "s{$i}",
                'body' => 'b',
                'channels' => null,
                'created_at' => now()->subSeconds($i),
            ]);
        }

        $this->getJson('/api/fleet/alerts?limit=999', ['Authorization' => 'Bearer tok'])
            ->assertOk()
            ->assertJsonPath('filters.limit', 100);
    }
}
