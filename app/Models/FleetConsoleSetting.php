<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetConsoleSetting extends Model
{
    protected $table = 'fleet_console_settings';

    protected $fillable = [
        'password_hash',
        'alert_email',
        'alert_slack_webhook',
        'alert_on_recovery',
        'alert_metric_rules',
        'alert_slo_min_ok_percent',
        'alert_slo_dedupe_hours',
        'alert_webhook_urls',
        'http_verify',
        'daily_rollup_sparkline_after_samples',
        'api_token',
        'trusted_ips',
        'health_token',
        'background_poll_enabled',
        'poll_interval_minutes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alert_on_recovery' => 'boolean',
            'alert_metric_rules' => 'array',
            'alert_webhook_urls' => 'array',
            'alert_slo_min_ok_percent' => 'float',
            'alert_slo_dedupe_hours' => 'integer',
            'http_verify' => 'boolean',
            'daily_rollup_sparkline_after_samples' => 'integer',
            'api_token' => 'encrypted',
            'health_token' => 'encrypted',
            'background_poll_enabled' => 'boolean',
            'poll_interval_minutes' => 'integer',
        ];
    }
}
