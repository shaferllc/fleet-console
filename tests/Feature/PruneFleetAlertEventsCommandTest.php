<?php

namespace Tests\Feature;

use App\Models\FleetAlertEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneFleetAlertEventsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_events_older_than_retention(): void
    {
        FleetAlertEvent::query()->create([
            'target_key' => 'a',
            'type' => 'down',
            'subject' => 'old',
            'body' => 'x',
            'channels' => 'slack',
            'created_at' => now()->subDays(100),
        ]);
        FleetAlertEvent::query()->create([
            'target_key' => 'b',
            'type' => 'down',
            'subject' => 'new',
            'body' => 'y',
            'channels' => 'slack',
            'created_at' => now()->subDay(),
        ]);

        $this->artisan('fleet:prune-alert-events', ['--days' => 90])
            ->assertSuccessful();

        $this->assertDatabaseMissing('fleet_alert_events', ['subject' => 'old']);
        $this->assertDatabaseHas('fleet_alert_events', ['subject' => 'new']);
    }
}
