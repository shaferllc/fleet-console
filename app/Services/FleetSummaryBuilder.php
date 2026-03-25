<?php

namespace App\Services;

use App\Models\FleetPollSample;
use App\Support\FleetTargetPublicInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Read-only snapshot from stored polls + config (does not HTTP poll targets).
 */
class FleetSummaryBuilder
{
    public function __construct(
        private FleetPollHistory $history,
    ) {}

    /**
     * @param  list<string>|null  $onlyKeys
     * @return array<string, mixed>
     */
    public function build(?Carbon $since = null, ?array $onlyKeys = null): array
    {
        $counts = $this->history->terminalStatusCounts();
        $vis = $this->history->fleetVisibilitySnapshot();

        $targets = [];
        foreach (config('fleet_console.targets', []) as $target) {
            if (! is_array($target)) {
                continue;
            }
            $key = (string) ($target['key'] ?? '');
            if ($key === '') {
                continue;
            }

            if ($onlyKeys !== null && ! in_array($key, $onlyKeys, true)) {
                continue;
            }

            $name = (string) ($target['name'] ?? $key);
            $urls = FleetTargetPublicInfo::urls($target);

            $sample = FleetPollSample::query()
                ->where('target_key', $key)
                ->orderByDesc('id')
                ->first();

            if ($since !== null) {
                if ($sample === null || $sample->created_at === null || $sample->created_at->lt($since)) {
                    continue;
                }
            }

            $lat24 = $this->history->latencyPercentiles($key, 24);
            $lat7 = $this->history->latencyPercentilesSevenDaysOrRaw($key);

            $targets[] = [
                'key' => $key,
                'name' => $name,
                'latest_poll' => $sample === null ? null : [
                    'ok' => (bool) $sample->ok,
                    'http_status' => $sample->http_status,
                    'latency_ms' => $sample->latency_ms,
                    'polled_at' => $sample->created_at?->toIso8601String(),
                    'error_excerpt' => $sample->error_message ? Str::limit((string) $sample->error_message, 240) : null,
                ],
                'slo' => [
                    'ok_percent_24h' => $this->history->availabilityPercent($key, 24),
                    'ok_percent_7d' => $this->history->availabilityPercent($key, 168),
                ],
                'latency' => [
                    'p50_ms_24h' => $lat24['p50'],
                    'p95_ms_24h' => $lat24['p95'],
                    'samples_24h' => $lat24['count'],
                    'p50_ms_7d' => $lat7['p50'],
                    'p95_ms_7d' => $lat7['p95'],
                    'samples_7d' => $lat7['count'],
                ],
                'urls' => $urls,
            ];
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'source' => 'stored_polls',
            'filters' => [
                'since' => $since?->toIso8601String(),
                'keys' => $onlyKeys,
            ],
            'fleet' => [
                'targets_total' => $counts['total'],
                'targets_ok_latest_poll' => $counts['ok'],
                'targets_err_latest_poll' => $counts['err'],
                'polls_24h' => $vis['fleet_samples_24h'],
                'polls_7d' => $vis['fleet_samples_7d'],
                'ok_percent_24h' => $vis['fleet_ok_24h'],
                'ok_percent_7d' => $vis['fleet_ok_7d'],
            ],
            'targets' => $targets,
        ];
    }

}
