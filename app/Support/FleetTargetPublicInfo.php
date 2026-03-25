<?php

namespace App\Support;

/**
 * Public, non-secret fields and derived URLs for fleet targets (UI + read API).
 */
class FleetTargetPublicInfo
{
    /**
     * @param  array<string, mixed>  $target
     * @return array{site: string|null, operator_summary: string|null, operator_readme: string|null}
     */
    public static function urls(array $target): array
    {
        $baseUrl = rtrim((string) ($target['base_url'] ?? ''), '/');
        $operatorPrefix = (string) ($target['operator_path_prefix'] ?? '/api/operator');
        $operatorPrefix = '/'.ltrim(rtrim($operatorPrefix, '/'), '/');

        $rawSite = $target['site_url'] ?? null;
        $siteUrl = $baseUrl !== '' ? rtrim((is_string($rawSite) && $rawSite !== '') ? $rawSite : $baseUrl, '/') : null;

        if ($baseUrl === '') {
            return [
                'site' => $siteUrl,
                'operator_summary' => null,
                'operator_readme' => null,
            ];
        }

        return [
            'site' => $siteUrl,
            'operator_summary' => $baseUrl.$operatorPrefix.'/summary',
            'operator_readme' => $baseUrl.$operatorPrefix.'/readme',
        ];
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>|null
     */
    public static function apiListRow(array $target): ?array
    {
        $key = (string) ($target['key'] ?? '');
        if ($key === '') {
            return null;
        }

        $name = (string) ($target['name'] ?? $key);
        $base = rtrim((string) ($target['base_url'] ?? ''), '/');
        $rawDesc = $target['description'] ?? null;
        $description = is_string($rawDesc) ? trim($rawDesc) : '';
        $operatorPrefix = (string) ($target['operator_path_prefix'] ?? '/api/operator');
        $operatorPrefix = '/'.ltrim(rtrim($operatorPrefix, '/'), '/');
        $rawSite = $target['site_url'] ?? null;
        $siteUrl = $base !== '' ? rtrim((is_string($rawSite) && $rawSite !== '') ? $rawSite : $base, '/') : null;

        return [
            'key' => $key,
            'name' => $name,
            'base_url' => $base !== '' ? $base : null,
            'site_url' => $siteUrl,
            'description' => $description !== '' ? $description : null,
            'operator_path_prefix' => $operatorPrefix,
            'urls' => self::urls($target),
        ];
    }
}
