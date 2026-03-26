<?php

namespace App\Models;

use App\Services\FleetTargetPoller;
use Illuminate\Database\Eloquent\Model;

class FleetTarget extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'base_url',
        'site_url',
        'staging_site_url',
        'operator_path_prefix',
        'operator_token',
        'sort_order',
        'is_enabled',
        'mute_alerts',
        'alert_slo_min_ok_percent',
        'alert_slo_dedupe_hours',
        'alert_webhook_urls',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'mute_alerts' => 'boolean',
            'operator_token' => 'encrypted',
            'alert_webhook_urls' => 'array',
            'alert_slo_min_ok_percent' => 'float',
            'alert_slo_dedupe_hours' => 'integer',
        ];
    }

    /**
     * Shape expected by {@see FleetTargetPoller} and config consumers.
     *
     * @return array<string, mixed>
     */
    public function asConfigRow(): array
    {
        $prefix = (string) $this->operator_path_prefix;
        $prefix = '/'.ltrim(rtrim($prefix, '/'), '/');

        $row = [
            'key' => $this->key,
            'name' => $this->name,
            'base_url' => rtrim((string) $this->base_url, '/'),
            'operator_path_prefix' => $prefix,
        ];

        $desc = is_string($this->description) ? trim($this->description) : '';
        if ($desc !== '') {
            $row['description'] = $desc;
        }

        $site = $this->site_url;
        if (is_string($site) && $site !== '') {
            $row['site_url'] = rtrim($site, '/');
        }

        $staging = $this->staging_site_url;
        if (is_string($staging) && $staging !== '') {
            $row['staging_site_url'] = rtrim($staging, '/');
        }

        $tok = $this->operator_token;
        if (is_string($tok) && $tok !== '') {
            $row['operator_token'] = $tok;
        }

        return $row;
    }
}
