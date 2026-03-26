<?php

namespace App\Services;

use App\Models\FleetAlertEvent;
use App\Models\FleetPollSample;
use App\Support\FleetTargetOverrides;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FleetAlertDispatcher
{
    public function dispatch(
        string $targetKey,
        string $targetName,
        ?FleetPollSample $previous,
        bool $currentOk,
        ?int $httpStatus,
        ?string $errorMessage,
        ?array $summary,
    ): void {
        if (! FleetTargetOverrides::muteAlerts($targetKey) && $previous !== null) {
            if ($previous->ok && ! $currentOk) {
                $this->sendAll(
                    "Fleet: {$targetName} ({$targetKey}) is DOWN",
                    $this->downBody($httpStatus, $errorMessage),
                    $targetKey,
                    'down',
                );
            } elseif (! $previous->ok && $currentOk && config('fleet_console.alert_on_recovery')) {
                $this->sendAll(
                    "Fleet: {$targetName} ({$targetKey}) is back UP",
                    'Operator summary is OK again.',
                    $targetKey,
                    'recovery',
                );
            }
        }

        if ($currentOk && is_array($summary)) {
            $this->maybeMetricAlerts($targetKey, $targetName, $summary, $previous?->summary_snapshot);
        }
    }

    /**
     * Alert when rolling 24h OK% from stored polls falls below minimum (per-target or global, deduped).
     */
    public function maybeSloBreach(string $targetKey, string $targetName, ?float $okPercent24h): void
    {
        if (FleetTargetOverrides::muteAlerts($targetKey)) {
            return;
        }

        $min = FleetTargetOverrides::sloMinOkPercent($targetKey);
        if ($min === null) {
            $minRaw = config('fleet_console.alert_slo_min_ok_percent');
            $min = is_numeric($minRaw) ? (float) $minRaw : null;
        }
        if ($min === null) {
            return;
        }

        if ($okPercent24h === null) {
            return;
        }

        if ($okPercent24h >= $min) {
            return;
        }

        $dedupeHours = FleetTargetOverrides::sloDedupeHours($targetKey)
            ?? max(1, (int) config('fleet_console.alert_slo_dedupe_hours', 6));
        $dedupeKey = 'fleet:slo:breach:'.sha1($targetKey.'|'.now()->format('Y-m-d'));
        if (Cache::has($dedupeKey)) {
            return;
        }
        Cache::put($dedupeKey, true, now()->addHours($dedupeHours));

        $this->sendAll(
            "Fleet SLO: {$targetName} ({$targetKey}) below {$min}% OK (24h rolling)",
            'Observed '.$okPercent24h."% OK over stored polls in the last 24 hours.\n".
            'Tune Alert settings / per-service SLO overrides in the console, or fix target reliability.',
            $targetKey,
            'slo',
        );
    }

    /**
     * @param  array<string, mixed>|null  $prevSnapshot
     */
    private function maybeMetricAlerts(string $key, string $name, array $summary, ?array $prevSnapshot): void
    {
        if (FleetTargetOverrides::muteAlerts($key)) {
            return;
        }

        $rules = config('fleet_console.alert_metric_rules', []);
        if (! is_array($rules) || $rules === []) {
            return;
        }

        /** @var list<array{path: string, min?: float|int, max?: float|int}> $sets */
        $sets = array_merge($rules['*'] ?? [], $rules[$key] ?? []);

        foreach ($sets as $rule) {
            if (! is_array($rule) || empty($rule['path']) || ! is_string($rule['path'])) {
                continue;
            }

            $path = $rule['path'];
            $val = data_get($summary, $path);
            if ($val === null || ! is_numeric($val)) {
                continue;
            }

            $val = (float) $val;
            $violates = false;
            if (isset($rule['min']) && is_numeric($rule['min']) && $val < (float) $rule['min']) {
                $violates = true;
            }
            if (isset($rule['max']) && is_numeric($rule['max']) && $val > (float) $rule['max']) {
                $violates = true;
            }
            if (! $violates) {
                continue;
            }

            $prevVal = $prevSnapshot !== null ? data_get($prevSnapshot, $path) : null;
            if ($prevVal !== null && is_numeric($prevVal)) {
                $pv = (float) $prevVal;
                $wasViolating = false;
                if (isset($rule['min']) && is_numeric($rule['min']) && $pv < (float) $rule['min']) {
                    $wasViolating = true;
                }
                if (isset($rule['max']) && is_numeric($rule['max']) && $pv > (float) $rule['max']) {
                    $wasViolating = true;
                }
                if ($wasViolating) {
                    continue;
                }
            }

            $dedupeKey = 'fleet:malert:'.sha1($key.'|'.$path.'|'.json_encode($rule));
            if (Cache::has($dedupeKey)) {
                continue;
            }
            Cache::put($dedupeKey, true, now()->addHour());

            $this->sendAll(
                "Fleet metric: {$name} ({$key}) — {$path} out of range",
                'Value: '.$val."\nRule: ".json_encode($rule),
                $key,
                'metric',
            );
        }
    }

    private function downBody(?int $status, ?string $err): string
    {
        $parts = [];
        if ($status !== null) {
            $parts[] = 'HTTP '.$status;
        }
        if ($err) {
            $parts[] = Str::limit($err, 500);
        }

        return $parts === [] ? 'No response' : implode("\n\n", $parts);
    }

    private function sendAll(string $subject, string $body, ?string $targetKey, string $type): void
    {
        $email = config('fleet_console.alert_email');
        $slack = config('fleet_console.alert_slack_webhook');
        /** @var list<string> $webhookUrls */
        $webhookUrls = array_values(array_unique(array_merge(
            config('fleet_console.alert_webhook_urls', []),
            $targetKey !== null ? FleetTargetOverrides::webhookUrlsForTarget($targetKey) : [],
        )));

        $hasDestinations = (is_string($email) && $email !== '')
            || (is_string($slack) && $slack !== '')
            || $webhookUrls !== [];

        if (! $hasDestinations) {
            return;
        }

        /** @var list<string> $channels */
        $channels = [];

        if (is_string($email) && $email !== '') {
            try {
                Mail::raw($body, function ($m) use ($email, $subject): void {
                    $m->to($email)->subject($subject);
                });
                $channels[] = 'email';
            } catch (\Throwable $e) {
                Log::warning('fleet_console.alert_mail_failed', ['exception' => $e->getMessage()]);
            }
        }

        if (is_string($slack) && $slack !== '') {
            try {
                Http::timeout(8)->post($slack, [
                    'text' => '*'.$subject."*\n```\n".$body."\n```",
                ]);
                $channels[] = 'slack';
            } catch (\Throwable $e) {
                Log::warning('fleet_console.alert_slack_failed', ['exception' => $e->getMessage()]);
            }
        }

        foreach ($webhookUrls as $url) {
            try {
                Http::timeout(8)->post($url, [
                    'event' => $type,
                    'target_key' => $targetKey,
                    'subject' => $subject,
                    'body' => $body,
                    'timestamp' => now()->toIso8601String(),
                ]);
                $channels[] = 'webhook';
            } catch (\Throwable $e) {
                Log::warning('fleet_console.alert_webhook_failed', ['url' => $url, 'exception' => $e->getMessage()]);
            }
        }

        FleetAlertEvent::query()->create([
            'target_key' => $targetKey,
            'type' => $type,
            'subject' => Str::limit($subject, 255),
            'body' => Str::limit($body, 8000),
            'channels' => $channels === [] ? 'none' : implode(',', array_unique($channels)),
            'created_at' => now(),
        ]);
    }
}
