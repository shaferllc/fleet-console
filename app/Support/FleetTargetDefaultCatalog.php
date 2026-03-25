<?php

namespace App\Support;

/**
 * Built-in product list from config/fleet_targets.php plus URL template env vars.
 * Used to seed the database when moving off file/env-only configuration.
 */
class FleetTargetDefaultCatalog
{
    public static function templateBaseUrlForKey(string $key): string
    {
        $template = rtrim((string) env('FLEET_CONSOLE_TARGET_URL_TEMPLATE', 'https://{key}.test'), '/');

        return str_replace('{key}', $key, $template);
    }

    /**
     * @return list<array{key: string, name: string, description: string, base_url: string, site_url: string|null, operator_path_prefix: string}>
     */
    public static function catalogRows(): array
    {
        $path = config_path('fleet_targets.php');
        if (! is_file($path)) {
            return [];
        }

        /** @var mixed $raw */
        $raw = require $path;
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $key = (string) ($row['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $name = (string) ($row['name'] ?? $key);
            $desc = '';
            if (isset($row['description']) && is_string($row['description'])) {
                $desc = trim($row['description']);
            }

            $prefix = '/api/operator';
            if (isset($row['operator_path_prefix']) && is_string($row['operator_path_prefix']) && $row['operator_path_prefix'] !== '') {
                $prefix = '/'.ltrim(rtrim($row['operator_path_prefix'], '/'), '/');
            }

            $siteUrl = null;
            if (isset($row['site_url']) && is_string($row['site_url']) && $row['site_url'] !== '') {
                $siteUrl = rtrim($row['site_url'], '/');
            }

            $out[] = [
                'key' => $key,
                'name' => $name,
                'description' => $desc,
                'base_url' => self::templateBaseUrlForKey($key),
                'site_url' => $siteUrl,
                'operator_path_prefix' => $prefix,
            ];
        }

        return $out;
    }
}
