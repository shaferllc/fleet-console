<?php

namespace App\Console\Commands;

use App\Models\FleetPollSample;
use App\Models\FleetPollDailyStat;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AggregateFleetPollDailyCommand extends Command
{
    protected $signature = 'fleet:aggregate-poll-daily {--date= : Stat date (Y-m-d), default yesterday in app timezone}';

    protected $description = 'Roll up fleet poll samples into per-target daily stats (for heavy 7d sparklines)';

    public function handle(): int
    {
        $tz = config('app.timezone', 'UTC');
        $raw = $this->option('date');
        if (is_string($raw) && $raw !== '') {
            $day = Carbon::parse($raw, $tz)->startOfDay();
        } else {
            $day = Carbon::now($tz)->subDay()->startOfDay();
        }

        $start = $day->copy();
        $end = $day->copy()->endOfDay();

        $keys = FleetPollSample::query()
            ->whereBetween('created_at', [$start, $end])
            ->distinct()
            ->pluck('target_key');

        $written = 0;

        foreach ($keys as $targetKey) {
            if (! is_string($targetKey) || $targetKey === '') {
                continue;
            }

            $latencies = FleetPollSample::query()
                ->where('target_key', $targetKey)
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('latency_ms')
                ->pluck('latency_ms')
                ->all();

            $samples = FleetPollSample::query()
                ->where('target_key', $targetKey)
                ->whereBetween('created_at', [$start, $end])
                ->get(['ok']);

            $n = $samples->count();
            if ($n === 0) {
                continue;
            }

            $okCount = $samples->where('ok', true)->count();

            $p50 = null;
            $p95 = null;
            $latN = count($latencies);
            if ($latN > 0) {
                sort($latencies);
                $p50 = $latencies[(int) floor(($latN - 1) * 0.50)];
                $p95 = $latencies[(int) floor(($latN - 1) * 0.95)];
            }

            FleetPollDailyStat::query()->updateOrCreate(
                [
                    'target_key' => $targetKey,
                    'stat_date' => $day->toDateString(),
                ],
                [
                    'sample_count' => $n,
                    'ok_count' => $okCount,
                    'latency_p50' => $p50,
                    'latency_p95' => $p95,
                    'aggregated_at' => now(),
                ],
            );
            $written++;
        }

        $this->info("Wrote {$written} daily row(s) for {$day->toDateString()}.");

        return self::SUCCESS;
    }
}
