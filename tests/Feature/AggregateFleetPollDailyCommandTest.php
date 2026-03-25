<?php

namespace Tests\Feature;

use App\Models\FleetPollDailyStat;
use App\Models\FleetPollSample;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateFleetPollDailyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_writes_daily_stats_for_yesterday(): void
    {
        $day = now()->subDay()->startOfDay();
        $at = $day->copy()->addHours(5);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => true,
            'http_status' => 200,
            'latency_ms' => 44,
            'error_message' => null,
            'summary_snapshot' => null,
            'created_at' => $at,
        ]);

        FleetPollSample::query()->create([
            'target_key' => 'alpha',
            'ok' => false,
            'http_status' => 500,
            'latency_ms' => 120,
            'error_message' => 'fail',
            'summary_snapshot' => null,
            'created_at' => $at->copy()->addMinute(),
        ]);

        $this->artisan('fleet:aggregate-poll-daily', ['--date' => $day->toDateString()])
            ->assertSuccessful();

        $row = FleetPollDailyStat::query()
            ->where('target_key', 'alpha')
            ->whereDate('stat_date', $day->toDateString())
            ->first();

        $this->assertNotNull($row);
        $this->assertSame(2, $row->sample_count);
        $this->assertSame(1, $row->ok_count);
        $this->assertNotNull($row->latency_p50);
        $this->assertNotNull($row->latency_p95);
    }
}
