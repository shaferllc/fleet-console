<?php

namespace App\Support;

use App\Models\FleetConsoleSetting;
use App\Models\FleetTarget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the full `fleet_console` runtime config (no `config/fleet_console.php` file).
 * Defaults are merged with {@see FleetConsoleSetting} and enabled {@see FleetTarget} rows.
 */
class FleetConsoleDynamicConfig
{
    public static function syncFromDatabase(): void
    {
        $fleet = self::baseDefaults();

        if (Schema::hasTable('fleet_console_settings')) {
            $row = FleetConsoleSetting::query()->first();
            if ($row !== null) {
                $fleet = self::mergeSettingsRow($fleet, $row);
            }
        }

        if (Schema::hasTable('fleet_targets')) {
            $allTargets = FleetTarget::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $fleet['target_overrides'] = self::targetOverridesFrom($allTargets);

            $enabled = $allTargets->where('is_enabled', true)->values();
            if ($enabled->isNotEmpty()) {
                $fleet['targets'] = $enabled
                    ->map(fn (FleetTarget $t) => $t->asConfigRow())
                    ->all();
            }
        }

        config(['fleet_console' => $fleet]);
    }

    /**
     * @deprecated Use {@see syncFromDatabase()}.
     */
    public static function syncTargetsFromDatabase(): void
    {
        self::syncFromDatabase();
    }

    /**
     * @return array<string, mixed>
     */
    private static function baseDefaults(): array
    {
        return [
            'password_hash' => env('FLEET_CONSOLE_PASSWORD_HASH', ''),
            'http_verify' => true,
            'targets' => [],
            'alert_email' => '',
            'alert_slack_webhook' => '',
            'alert_on_recovery' => false,
            'alert_metric_rules' => [],
            'alert_slo_min_ok_percent' => null,
            'alert_slo_dedupe_hours' => 6,
            'daily_rollup_sparkline_after_samples' => 800,
            'api_token' => '',
            'target_overrides' => [],
            'alert_webhook_urls' => [],
            'trusted_ips' => '',
            'health_token' => '',
            'background_poll_enabled' => false,
            'poll_interval_minutes' => 10,
        ];
    }

    /**
     * @param  array<string, mixed>  $fleet
     * @return array<string, mixed>
     */
    private static function mergeSettingsRow(array $fleet, FleetConsoleSetting $row): array
    {
        $rules = $row->alert_metric_rules;
        if (! is_array($rules)) {
            $rules = [];
        }

        $webhooks = $row->alert_webhook_urls;
        if (! is_array($webhooks)) {
            $webhooks = [];
        }
        $webhooks = array_values(array_unique(array_filter(
            $webhooks,
            static fn (mixed $u): bool => is_string($u) && filter_var($u, FILTER_VALIDATE_URL)
        )));

        $sloMin = $row->alert_slo_min_ok_percent;

        $fleet['http_verify'] = (bool) $row->http_verify;
        $fleet['daily_rollup_sparkline_after_samples'] = max(0, (int) $row->daily_rollup_sparkline_after_samples);
        $apiToken = $row->api_token;
        $fleet['api_token'] = is_string($apiToken) && $apiToken !== '' ? $apiToken : '';
        $trusted = $row->trusted_ips;
        $fleet['trusted_ips'] = is_string($trusted) ? trim($trusted) : '';
        $health = $row->health_token;
        $fleet['health_token'] = is_string($health) && $health !== '' ? $health : '';
        $fleet['background_poll_enabled'] = (bool) $row->background_poll_enabled;
        $fleet['poll_interval_minutes'] = max(1, min(120, (int) $row->poll_interval_minutes));

        $fleet['alert_email'] = is_string($row->alert_email) ? trim($row->alert_email) : '';
        $fleet['alert_slack_webhook'] = is_string($row->alert_slack_webhook) ? trim($row->alert_slack_webhook) : '';
        $fleet['alert_on_recovery'] = (bool) $row->alert_on_recovery;
        $fleet['alert_metric_rules'] = $rules;
        $fleet['alert_slo_min_ok_percent'] = is_numeric($sloMin) ? (float) $sloMin : null;
        $fleet['alert_slo_dedupe_hours'] = max(1, (int) $row->alert_slo_dedupe_hours);
        $fleet['alert_webhook_urls'] = $webhooks;

        $dbPassword = $row->password_hash ?? null;
        if (is_string($dbPassword) && $dbPassword !== '') {
            $fleet['password_hash'] = $dbPassword;
        }

        return $fleet;
    }

    /**
     * @param  Collection<int, FleetTarget>  $allTargets
     * @return array<string, array<string, mixed>>
     */
    private static function targetOverridesFrom(Collection $allTargets): array
    {
        $overrides = [];
        foreach ($allTargets as $t) {
            $piece = [];
            if ($t->mute_alerts) {
                $piece['mute_alerts'] = true;
            }
            if ($t->alert_slo_min_ok_percent !== null && is_numeric($t->alert_slo_min_ok_percent)) {
                $piece['alert_slo_min_ok_percent'] = (float) $t->alert_slo_min_ok_percent;
            }
            if ($t->alert_slo_dedupe_hours !== null && is_numeric($t->alert_slo_dedupe_hours)) {
                $piece['alert_slo_dedupe_hours'] = max(1, (int) $t->alert_slo_dedupe_hours);
            }
            $urls = $t->alert_webhook_urls;
            if (is_array($urls) && $urls !== []) {
                $filtered = array_values(array_filter(
                    $urls,
                    static fn (mixed $u): bool => is_string($u) && filter_var($u, FILTER_VALIDATE_URL)
                ));
                if ($filtered !== []) {
                    $piece['webhook_urls'] = $filtered;
                }
            }
            if ($piece !== []) {
                $overrides[$t->key] = $piece;
            }
        }

        return $overrides;
    }
}
