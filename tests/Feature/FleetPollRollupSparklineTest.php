<?php

namespace Tests\Feature;

use App\Models\FleetPollSample;
use App\Services\FleetPollHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetPollRollupSparklineTest extends TestCase
{
    use RefreshDatabase;

    public function test_seven_day_sparkline_uses_daily_buckets_when_over_threshold(): void
    {
        config(['fleet_console.daily_rollup_sparkline_after_samples' => 5]);

        $key = 'heavy';
        $base = now()->subDays(3)->startOfDay();

        for ($i = 0; $i < 6; $i++) {
            FleetPollSample::query()->create([
                'target_key' => $key,
                'ok' => true,
                'http_status' => 200,
                'latency_ms' => 10 + $i,
                'error_message' => null,
                'summary_snapshot' => null,
                'created_at' => $base->copy()->addHours($i),
            ]);
        }

        $history = app(FleetPollHistory::class);
        $bits = $history->sparklineBits($key, 168, 60);

        $this->assertCount(60, $bits);
        foreach ($bits as $b) {
            $this->assertContains($b, [0, 1]);
        }
    }
}
