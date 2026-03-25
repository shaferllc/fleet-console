<?php

namespace App\Services;

use App\Models\FleetPollDailyStat;
use App\Models\FleetPollSample;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FleetPollHistory
{
    /**
     * Weighted OK% across all targets in the window (stored polls only).
     */
    public function fleetWideAvailabilityPercent(int $hours): ?float
    {
        $since = Carbon::now()->subHours($hours);
        $samples = FleetPollSample::query()
            ->where('created_at', '>=', $since)
            ->get(['ok']);

        $total = $samples->count();
        if ($total === 0) {
            return null;
        }

        $ok = $samples->where('ok', true)->count();

        return round(100 * $ok / $total, 2);
    }

    public function fleetWideSampleCount(int $hours): int
    {
        $since = Carbon::now()->subHours($hours);

        return (int) FleetPollSample::query()
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * @return array<string, string|\DateTimeInterface|null> target_key => last created_at
     */
    public function latestSampleTimesByTarget(): array
    {
        /** @var \Illuminate\Support\Collection<string, string|null> $rows */
        $rows = FleetPollSample::query()
            ->selectRaw('target_key, MAX(created_at) as last_at')
            ->groupBy('target_key')
            ->pluck('last_at', 'target_key');

        return $rows->all();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public function attachLastPollAtToRows(array $rows): array
    {
        $lastByKey = $this->latestSampleTimesByTarget();

        return array_map(function (array $row) use ($lastByKey): array {
            $k = (string) ($row['key'] ?? '');
            if ($k === '' || $k === '—') {
                return array_merge($row, ['last_poll_at' => null]);
            }
            $raw = $lastByKey[$k] ?? null;

            return array_merge($row, [
                'last_poll_at' => $raw !== null ? Carbon::parse($raw)->toIso8601String() : null,
            ]);
        }, $rows);
    }

    /**
     * @return array{fleet_ok_24h: float|null, fleet_ok_7d: float|null, fleet_samples_24h: int, fleet_samples_7d: int}
     */
    public function fleetVisibilitySnapshot(): array
    {
        return [
            'fleet_ok_24h' => $this->fleetWideAvailabilityPercent(24),
            'fleet_ok_7d' => $this->fleetWideAvailabilityPercent(168),
            'fleet_samples_24h' => $this->fleetWideSampleCount(24),
            'fleet_samples_7d' => $this->fleetWideSampleCount(168),
        ];
    }

    /**
     * Targets / healthy / errors from the latest stored poll per configured target (no extra HTTP).
     *
     * @return array{total: int, ok: int, err: int}
     */
    public function terminalStatusCounts(): array
    {
        $keys = [];
        foreach (config('fleet_console.targets', []) as $target) {
            if (! is_array($target)) {
                continue;
            }
            $k = (string) ($target['key'] ?? '');
            if ($k !== '') {
                $keys[] = $k;
            }
        }

        $total = count($keys);
        if ($total === 0) {
            return ['total' => 0, 'ok' => 0, 'err' => 0];
        }

        $latest = FleetPollSample::query()
            ->select('fleet_poll_samples.target_key', 'fleet_poll_samples.ok')
            ->from('fleet_poll_samples')
            ->joinSub(
                FleetPollSample::query()
                    ->selectRaw('target_key, MAX(id) as max_id')
                    ->whereIn('target_key', $keys)
                    ->groupBy('target_key'),
                'm',
                fn ($join) => $join->on('fleet_poll_samples.id', '=', 'm.max_id')
            )
            ->pluck('ok', 'target_key');

        $ok = 0;
        foreach ($keys as $k) {
            if (isset($latest[$k]) && $latest[$k]) {
                $ok++;
            }
        }

        return ['total' => $total, 'ok' => $ok, 'err' => $total - $ok];
    }

    /**
     * Percent of OK polls in the window, or null if no samples.
     */
    public function availabilityPercent(string $targetKey, int $hours): ?float
    {
        $since = Carbon::now()->subHours($hours);
        $samples = FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->where('created_at', '>=', $since)
            ->get(['ok']);

        $total = $samples->count();
        if ($total === 0) {
            return null;
        }

        $ok = $samples->where('ok', true)->count();

        return round(100 * $ok / $total, 2);
    }

    /**
     * Per-target drill-down JSON (console poll-detail + read API). Null if key is not configured.
     *
     * @return array<string, mixed>|null
     */
    public function targetDetailPayload(string $key): ?array
    {
        $name = null;
        foreach (config('fleet_console.targets', []) as $target) {
            if (! is_array($target)) {
                continue;
            }
            if ((string) ($target['key'] ?? '') === $key) {
                $name = (string) ($target['name'] ?? $key);
                break;
            }
        }

        if ($name === null) {
            return null;
        }

        $rollupThreshold = (int) config('fleet_console.daily_rollup_sparkline_after_samples', 800);
        $raw7 = $this->rawSampleCount($key, 168);

        return [
            'key' => $key,
            'name' => $name,
            'last_ok_summary' => $this->lastOkSummarySnapshot($key),
            'recent_errors' => $this->recentErrors($key, 15),
            'recent_polls' => $this->recentPollsTable($key, 25),
            'slo_24h' => $this->availabilityPercent($key, 24),
            'slo_7d' => $this->availabilityPercent($key, 24 * 7),
            'sparkline_24h' => $this->sparklineBits($key, 24),
            'sparkline_7d' => $this->sparklineBits($key, 24 * 7),
            'sparkline_7d_rollups' => $rollupThreshold > 0 && $raw7 >= $rollupThreshold,
        ];
    }

    /**
     * Latest successful operator summary JSON stored on a sample.
     *
     * @return array<string, mixed>|null
     */
    public function lastOkSummarySnapshot(string $targetKey): ?array
    {
        $sample = FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->where('ok', true)
            ->whereNotNull('summary_snapshot')
            ->orderByDesc('id')
            ->first();

        $snap = $sample?->summary_snapshot;

        return is_array($snap) ? $snap : null;
    }

    /**
     * @return list<array{at: string, http_status: int|null, message: string}>
     */
    public function recentErrors(string $targetKey, int $limit = 12): array
    {
        return FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->where('ok', false)
            ->orderByDesc('id')
            ->limit(max(1, min(50, $limit)))
            ->get(['created_at', 'http_status', 'error_message'])
            ->map(fn (FleetPollSample $s): array => [
                'at' => $s->created_at?->toIso8601String() ?? '',
                'http_status' => $s->http_status,
                'message' => Str::limit((string) ($s->error_message ?? ''), 2000),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{at: string, ok: bool, latency_ms: int|null, http_status: int|null, error_excerpt: string|null}>
     */
    public function recentPollsTable(string $targetKey, int $limit = 25): array
    {
        return FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->orderByDesc('id')
            ->limit(max(1, min(100, $limit)))
            ->get(['created_at', 'ok', 'latency_ms', 'http_status', 'error_message'])
            ->map(fn (FleetPollSample $s): array => [
                'at' => $s->created_at?->toIso8601String() ?? '',
                'ok' => (bool) $s->ok,
                'latency_ms' => $s->latency_ms,
                'http_status' => $s->http_status,
                'error_excerpt' => $s->error_message ? Str::limit((string) $s->error_message, 160) : null,
            ])
            ->values()
            ->all();
    }

    /**
     * Raw poll rows in the rolling window (for rollup thresholds).
     */
    public function rawSampleCount(string $targetKey, int $hours): int
    {
        $since = Carbon::now()->subHours($hours);

        return (int) FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * @return list<int> 1 = OK, 0 = error (last 24h, downsampled)
     */
    public function sparklineBits(string $targetKey, int $hours = 24, int $maxPoints = 120): array
    {
        $since = Carbon::now()->subHours($hours);

        if ($hours >= 168) {
            $threshold = (int) config('fleet_console.daily_rollup_sparkline_after_samples', 800);
            if ($threshold > 0 && $this->rawSampleCount($targetKey, $hours) >= $threshold) {
                return $this->sparklineBitsSevenDaysFromRollups($targetKey, $maxPoints);
            }
        }

        $samples = FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at')
            ->get(['ok']);

        if ($samples->isEmpty()) {
            return [];
        }

        /** @var list<int> $bits */
        $bits = $samples->map(fn (FleetPollSample $s): int => $s->ok ? 1 : 0)->all();
        $n = count($bits);
        if ($n <= $maxPoints) {
            return $bits;
        }

        $step = $n / $maxPoints;
        $out = [];
        for ($i = 0; $i < $maxPoints; $i++) {
            $idx = (int) floor($i * $step);
            $out[] = $bits[min($idx, $n - 1)];
        }

        return $out;
    }

    /**
     * When 7d raw volume is high, one bit bucket per calendar day (expanded to maxPoints) using daily rollups + per-day raw fallback.
     *
     * @return list<int>
     */
    public function sparklineBitsSevenDaysFromRollups(string $targetKey, int $maxPoints = 120): array
    {
        $sizes = $this->distributePointsAcrossBuckets(7, $maxPoints);
        /** @var list<int> $out */
        $out = [];

        for ($i = 0; $i < 7; $i++) {
            $dayStart = Carbon::now()->copy()->subDays(6 - $i)->startOfDay();
            $dayEnd = $i === 6 ? Carbon::now() : $dayStart->copy()->endOfDay();
            $bit = $this->pollDayHealthBit($targetKey, $dayStart, $dayEnd);
            for ($k = 0; $k < $sizes[$i]; $k++) {
                $out[] = $bit;
            }
        }

        return $out;
    }

    /**
     * @return list<int>
     */
    private function distributePointsAcrossBuckets(int $buckets, int $total): array
    {
        if ($buckets <= 0 || $total <= 0) {
            return [];
        }

        $base = intdiv($total, $buckets);
        $rem = $total % $buckets;
        $sizes = array_fill(0, $buckets, $base);
        for ($i = 0; $i < $rem; $i++) {
            $sizes[$i]++;
        }

        return $sizes;
    }

    private function pollDayHealthBit(string $targetKey, Carbon $dayStart, Carbon $dayEnd): int
    {
        if ($dayEnd->lt(Carbon::now()->startOfDay())) {
            $stat = FleetPollDailyStat::query()
                ->where('target_key', $targetKey)
                ->whereDate('stat_date', $dayStart->toDateString())
                ->first();
            if ($stat !== null && $stat->sample_count > 0) {
                return $stat->ok_count === $stat->sample_count ? 1 : 0;
            }
        }

        $samples = FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->get(['ok']);

        if ($samples->isEmpty()) {
            return 1;
        }

        return $samples->contains(fn (FleetPollSample $s): bool => ! $s->ok) ? 0 : 1;
    }

    /**
     * @return array{p50: int|null, p95: int|null, count: int, last: int|null}
     */
    public function latencyPercentiles(string $targetKey, int $hours = 24): array
    {
        $since = Carbon::now()->subHours($hours);
        $latencies = FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->where('created_at', '>=', $since)
            ->whereNotNull('latency_ms')
            ->pluck('latency_ms')
            ->all();

        $n = count($latencies);
        if ($n === 0) {
            return [
                'p50' => null,
                'p95' => null,
                'count' => 0,
                'last' => FleetPollSample::query()->where('target_key', $targetKey)->orderByDesc('id')->value('latency_ms'),
            ];
        }

        sort($latencies);
        $p50 = $latencies[(int) floor(($n - 1) * 0.50)];
        $p95 = $latencies[(int) floor(($n - 1) * 0.95)];
        $last = FleetPollSample::query()->where('target_key', $targetKey)->orderByDesc('id')->value('latency_ms');

        return [
            'p50' => $p50,
            'p95' => $p95,
            'count' => $n,
            'last' => $last,
        ];
    }

    /**
     * 7d latency: raw samples below threshold use exact percentiles; above uses daily rollups + today’s raw (weighted heuristic).
     *
     * @return array{p50: int|null, p95: int|null, count: int, last: int|null}
     */
    public function latencyPercentilesSevenDaysOrRaw(string $targetKey): array
    {
        $threshold = (int) config('fleet_console.daily_rollup_sparkline_after_samples', 800);
        if ($threshold <= 0 || $this->rawSampleCount($targetKey, 168) < $threshold) {
            return $this->latencyPercentiles($targetKey, 168);
        }

        $rollup = $this->latencyPercentilesFromSevenDayRollups($targetKey);
        if ($rollup['count'] > 0) {
            return $rollup;
        }

        return $this->latencyPercentiles($targetKey, 168);
    }

    /**
     * @return array{p50: int|null, p95: int|null, count: int, last: int|null}
     */
    private function latencyPercentilesFromSevenDayRollups(string $targetKey): array
    {
        $last = FleetPollSample::query()->where('target_key', $targetKey)->orderByDesc('id')->value('latency_ms');

        $startWeek = Carbon::now()->copy()->subDays(6)->startOfDay();
        $stats = FleetPollDailyStat::query()
            ->where('target_key', $targetKey)
            ->where('stat_date', '>=', $startWeek->toDateString())
            ->where('stat_date', '<', Carbon::now()->startOfDay()->toDateString())
            ->orderBy('stat_date')
            ->get();

        $todayStart = Carbon::now()->startOfDay();
        $todayLat = FleetPollSample::query()
            ->where('target_key', $targetKey)
            ->where('created_at', '>=', $todayStart)
            ->whereNotNull('latency_ms')
            ->pluck('latency_ms')
            ->all();

        $tw = count($todayLat);
        $tp50 = null;
        $tp95 = null;
        if ($tw > 0) {
            sort($todayLat);
            $tp50 = $todayLat[(int) floor(($tw - 1) * 0.50)];
            $tp95 = $todayLat[(int) floor(($tw - 1) * 0.95)];
        }

        $sumW = 0;
        $sumP50 = 0.0;
        $sumP95 = 0.0;

        foreach ($stats as $s) {
            if ($s->sample_count <= 0 || $s->latency_p50 === null) {
                continue;
            }
            $w = $s->sample_count;
            $sumW += $w;
            $sumP50 += $s->latency_p50 * $w;
            $sumP95 += ($s->latency_p95 ?? $s->latency_p50) * $w;
        }

        if ($tw > 0 && $tp50 !== null) {
            $sumW += $tw;
            $sumP50 += $tp50 * $tw;
            $sumP95 += ($tp95 ?? $tp50) * $tw;
        }

        if ($sumW === 0) {
            return [
                'p50' => null,
                'p95' => null,
                'count' => 0,
                'last' => $last,
            ];
        }

        return [
            'p50' => (int) round($sumP50 / $sumW),
            'p95' => (int) round($sumP95 / $sumW),
            'count' => $sumW,
            'last' => $last,
        ];
    }
}
