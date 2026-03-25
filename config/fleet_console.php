<?php

$targetRows = require __DIR__.'/fleet_targets.php';
$template = rtrim((string) env('FLEET_CONSOLE_TARGET_URL_TEMPLATE', 'https://{key}.test'), '/');

$defaultTargets = array_map(static function (array $row) use ($template): array {
    $out = [
        'key' => $row['key'],
        'name' => $row['name'],
        'base_url' => str_replace('{key}', $row['key'], $template),
    ];
    foreach (['description', 'operator_path_prefix', 'operator_token', 'site_url', 'staging_site_url'] as $optional) {
        if (array_key_exists($optional, $row)) {
            $out[$optional] = $row[$optional];
        }
    }

    return $out;
}, $targetRows);

$rawTargets = env('FLEET_CONSOLE_TARGETS');
$envTargets = [];
if ($rawTargets !== null && $rawTargets !== '') {
    $decoded = json_decode((string) $rawTargets, true);
    $envTargets = is_array($decoded) ? $decoded : [];
}

$targets = count($envTargets) > 0 ? $envTargets : $defaultTargets;

$targets = array_map(static function (array $t): array {
    $raw = $t['operator_path_prefix'] ?? null;
    if (! is_string($raw) || $raw === '') {
        $raw = '/api/operator';
    }
    $t['operator_path_prefix'] = '/'.ltrim(rtrim($raw, '/'), '/');

    return $t;
}, $targets);

$metricRules = json_decode((string) env('FLEET_ALERT_METRIC_RULES', '{}'), true);

$sloMin = env('FLEET_ALERT_SLO_MIN_OK_PERCENT');

$targetOverridesRaw = env('FLEET_CONSOLE_TARGET_OVERRIDES');
$targetOverrides = [];
if (is_string($targetOverridesRaw) && $targetOverridesRaw !== '') {
    $decodedOverrides = json_decode($targetOverridesRaw, true);
    $targetOverrides = is_array($decodedOverrides) ? $decodedOverrides : [];
}

$alertWebhookUrls = [];
$multiWebhookRaw = env('FLEET_ALERT_WEBHOOK_URLS');
if (is_string($multiWebhookRaw) && $multiWebhookRaw !== '') {
    $decodedHooks = json_decode($multiWebhookRaw, true);
    if (is_array($decodedHooks)) {
        foreach ($decodedHooks as $u) {
            if (is_string($u) && filter_var($u, FILTER_VALIDATE_URL)) {
                $alertWebhookUrls[] = $u;
            }
        }
    }
}
$singleHook = env('FLEET_ALERT_WEBHOOK_URL');
if (is_string($singleHook) && $singleHook !== '' && filter_var($singleHook, FILTER_VALIDATE_URL)) {
    $alertWebhookUrls[] = $singleHook;
}
$alertWebhookUrls = array_values(array_unique($alertWebhookUrls));

return [
    'password' => env('FLEET_CONSOLE_PASSWORD', ''),
    'password_hash' => env('FLEET_CONSOLE_PASSWORD_HASH', ''),
    'http_verify' => filter_var(env('FLEET_CONSOLE_HTTP_VERIFY', true), FILTER_VALIDATE_BOOL),
    'targets' => $targets,
    'alert_email' => env('FLEET_ALERT_EMAIL', ''),
    'alert_slack_webhook' => env('FLEET_ALERT_SLACK_WEBHOOK', ''),
    'alert_on_recovery' => filter_var(env('FLEET_ALERT_ON_RECOVERY', false), FILTER_VALIDATE_BOOL),
    'alert_metric_rules' => is_array($metricRules) ? $metricRules : [],
    'alert_slo_min_ok_percent' => $sloMin !== null && $sloMin !== '' && is_numeric($sloMin) ? (float) $sloMin : null,
    'alert_slo_dedupe_hours' => max(1, (int) env('FLEET_ALERT_SLO_DEDUPE_HOURS', 6)),
    'daily_rollup_sparkline_after_samples' => max(0, (int) env('FLEET_DAILY_ROLLUP_SPARKLINE_AFTER_SAMPLES', 800)),
    'api_token' => env('FLEET_CONSOLE_API_TOKEN', ''),
    'target_overrides' => $targetOverrides,
    'alert_webhook_urls' => $alertWebhookUrls,
    'trusted_ips' => env('FLEET_CONSOLE_TRUSTED_IPS', ''),
    'health_token' => env('FLEET_HEALTH_TOKEN', ''),
    'background_poll_enabled' => filter_var(env('FLEET_BACKGROUND_POLL_ENABLED', false), FILTER_VALIDATE_BOOL),
    'poll_interval_minutes' => max(1, min(120, (int) env('FLEET_POLL_INTERVAL_MINUTES', 10))),
];
