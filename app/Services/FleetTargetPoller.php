<?php

namespace App\Services;

use App\Models\FleetPollSample;
use Illuminate\Support\Facades\Http;

class FleetTargetPoller
{
    public function __construct(
        private FleetPollHistory $history,
        private FleetAlertDispatcher $alerts,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function pollAll(): array
    {
        $targets = config('fleet_console.targets', []);
        $defaultToken = config('fleet_console.operator_token');
        $out = [];
        foreach ($targets as $target) {
            if (! is_array($target)) {
                continue;
            }
            $out[] = $this->pollTarget($target, $defaultToken);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pollTargetByKey(string $key): ?array
    {
        $targets = config('fleet_console.targets', []);
        $defaultToken = config('fleet_console.operator_token');
        foreach ($targets as $target) {
            if (! is_array($target) || (string) ($target['key'] ?? '') !== $key) {
                continue;
            }

            return $this->pollTarget($target, $defaultToken);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    public function pollTarget(array $target, mixed $defaultToken): array
    {
        $key = (string) ($target['key'] ?? '');
        $name = (string) ($target['name'] ?? $key);
        $baseUrl = rtrim((string) ($target['base_url'] ?? ''), '/');
        $token = $target['operator_token'] ?? $defaultToken;
        $operatorPrefix = (string) ($target['operator_path_prefix'] ?? '/api/operator');
        $operatorPrefix = '/'.ltrim(rtrim($operatorPrefix, '/'), '/');

        $rawDesc = $target['description'] ?? null;
        $description = is_string($rawDesc) ? trim($rawDesc) : '';

        $rawSite = $target['site_url'] ?? null;
        $siteUrl = $baseUrl !== '' ? rtrim((is_string($rawSite) && $rawSite !== '') ? $rawSite : $baseUrl, '/') : '';

        $summaryUrl = $baseUrl !== '' ? $baseUrl.$operatorPrefix.'/summary' : '';
        $readmeUrl = $baseUrl !== '' ? $baseUrl.$operatorPrefix.'/readme' : '';

        $baseRow = [
            'key' => $key ?: '—',
            'name' => $name,
            'description' => $description,
            'base_url' => $baseUrl,
            'site_url' => $siteUrl,
            'operator_summary_url' => $summaryUrl,
            'operator_readme_url' => $readmeUrl,
        ];

        $tokenMissing = ! is_string($token) || $token === '';
        if ($key === '' || $baseUrl === '' || $tokenMissing) {
            $hk = $key !== '' ? $key : '—';
            $lat = $this->history->latencyPercentiles($hk, 24);
            $lat7 = $this->history->latencyPercentilesSevenDaysOrRaw($hk);
            $rollupThreshold = (int) config('fleet_console.daily_rollup_sparkline_after_samples', 800);
            $raw7 = $this->history->rawSampleCount($hk, 168);

            $missingParts = [];
            if ($key === '') {
                $missingParts[] = 'target key';
            }
            if ($baseUrl === '') {
                $missingParts[] = 'operator base URL';
            }
            if ($tokenMissing) {
                $missingParts[] = 'bearer token for this Fleet install — set FLEET_OPERATOR_TOKEN on the Fleet host or a per-target token under Console → Services (same secret as FLEET_OPERATOR_TOKEN on the target app; the operator package there only protects the API, it does not configure Fleet)';
            }
            $configError = count($missingParts) === 1
                ? 'Missing '.$missingParts[0].'.'
                : 'Missing: '.implode('; ', $missingParts).'.';

            return array_merge($baseRow, [
                'ok' => false,
                'status' => null,
                'summary' => null,
                'error' => $configError,
                'latency_ms' => null,
                'sparkline' => $this->history->sparklineBits($hk, 24),
                'sparkline_7d' => $this->history->sparklineBits($hk, 24 * 7),
                'sparkline_7d_rollups' => $rollupThreshold > 0 && $raw7 >= $rollupThreshold,
                'slo_24h' => $this->history->availabilityPercent($hk, 24),
                'slo_7d' => $this->history->availabilityPercent($hk, 24 * 7),
                'latency_p50' => $lat['p50'],
                'latency_p95' => $lat['p95'],
                'latency_last' => $lat['last'],
                'latency_samples' => $lat['count'],
                'latency_7d_p50' => $lat7['p50'],
                'latency_7d_p95' => $lat7['p95'],
                'latency_7d_samples' => $raw7,
            ]);
        }

        $previous = FleetPollSample::query()
            ->where('target_key', $key)
            ->orderByDesc('id')
            ->first();

        $url = $summaryUrl;
        $ok = false;
        $status = null;
        $summary = null;
        $error = null;
        $latencyMs = null;

        $started = microtime(true);

        try {
            $response = Http::timeout(12)
                ->withOptions(['verify' => (bool) config('fleet_console.http_verify', true)])
                ->withToken($token)
                ->acceptJson()
                ->get($url);

            $latencyMs = (int) round((microtime(true) - $started) * 1000);
            $ok = $response->successful();
            $status = $response->status();
            $summary = $response->successful() ? $response->json() : null;
            $error = $response->successful() ? null : $response->body();
        } catch (\Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $started) * 1000);
            $error = $e->getMessage();
        }

        FleetPollSample::query()->create([
            'target_key' => $key,
            'ok' => $ok,
            'http_status' => $status,
            'latency_ms' => $latencyMs,
            'error_message' => $error !== null ? mb_substr($error, 0, 65000) : null,
            'summary_snapshot' => is_array($summary) ? $summary : null,
            'created_at' => now(),
        ]);

        $this->alerts->dispatch(
            $key,
            $name,
            $previous,
            $ok,
            $status,
            $error,
            is_array($summary) ? $summary : null,
        );

        $slo24 = $this->history->availabilityPercent($key, 24);
        $this->alerts->maybeSloBreach($key, $name, $slo24);

        $lat = $this->history->latencyPercentiles($key, 24);
        $lat7 = $this->history->latencyPercentilesSevenDaysOrRaw($key);
        $rollupThreshold = (int) config('fleet_console.daily_rollup_sparkline_after_samples', 800);
        $raw7 = $this->history->rawSampleCount($key, 168);

        return array_merge($baseRow, [
            'key' => $key,
            'ok' => $ok,
            'status' => $status,
            'summary' => $summary,
            'error' => $error,
            'latency_ms' => $latencyMs,
            'operator_summary_url' => $url,
            'sparkline' => $this->history->sparklineBits($key, 24),
            'sparkline_7d' => $this->history->sparklineBits($key, 24 * 7),
            'sparkline_7d_rollups' => $rollupThreshold > 0 && $raw7 >= $rollupThreshold,
            'slo_24h' => $slo24,
            'slo_7d' => $this->history->availabilityPercent($key, 24 * 7),
            'latency_p50' => $lat['p50'],
            'latency_p95' => $lat['p95'],
            'latency_last' => $lat['last'],
            'latency_samples' => $lat['count'],
            'latency_7d_p50' => $lat7['p50'],
            'latency_7d_p95' => $lat7['p95'],
            'latency_7d_samples' => $raw7,
        ]);
    }
}
