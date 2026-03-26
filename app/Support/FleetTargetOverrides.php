<?php

namespace App\Support;

/**
 * Per-target overrides merged from {@see FleetTarget} rows into config during {@see FleetConsoleDynamicConfig::syncFromDatabase()}.
 *
 * @phpstan-type OverrideShape array{
 *     mute_alerts?: bool|string,
 *     alert_slo_min_ok_percent?: float|int|string,
 *     alert_slo_dedupe_hours?: int|string,
 *     webhook_urls?: list<string|mixed>
 * }
 */
class FleetTargetOverrides
{
    /**
     * @return OverrideShape
     */
    public static function forKey(string $key): array
    {
        $all = config('fleet_console.target_overrides', []);
        if (! is_array($all)) {
            return [];
        }
        $row = $all[$key] ?? null;

        return is_array($row) ? $row : [];
    }

    public static function muteAlerts(string $key): bool
    {
        return filter_var(static::forKey($key)['mute_alerts'] ?? false, FILTER_VALIDATE_BOOL);
    }

    public static function sloMinOkPercent(string $key): ?float
    {
        $v = static::forKey($key)['alert_slo_min_ok_percent'] ?? null;

        return is_numeric($v) ? (float) $v : null;
    }

    public static function sloDedupeHours(string $key): ?int
    {
        $v = static::forKey($key)['alert_slo_dedupe_hours'] ?? null;

        return is_numeric($v) ? max(1, (int) $v) : null;
    }

    /**
     * @return list<string>
     */
    public static function webhookUrlsForTarget(string $key): array
    {
        $raw = static::forKey($key)['webhook_urls'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $u) {
            if (is_string($u) && filter_var($u, FILTER_VALIDATE_URL)) {
                $out[] = $u;
            }
        }

        return $out;
    }
}
