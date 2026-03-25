<?php

namespace App\Support;

final class FleetOperatorSummaryDisplay
{
    /**
     * Human-readable uptime from integer seconds (e.g. "3d 4h 2m").
     */
    public static function uptimeLabel(mixed $seconds): ?string
    {
        if (! is_numeric($seconds)) {
            return null;
        }

        $s = max(0, (int) $seconds);
        $d = intdiv($s, 86400);
        $h = intdiv($s % 86400, 3600);
        $m = intdiv($s % 3600, 60);

        $parts = [];
        if ($d > 0) {
            $parts[] = $d.'d';
        }
        if ($h > 0 || $d > 0) {
            $parts[] = $h.'h';
        }
        $parts[] = $m.'m';

        return implode(' ', $parts);
    }

    public static function shortCommit(mixed $sha): ?string
    {
        if (! is_string($sha)) {
            return null;
        }
        $t = trim($sha);
        if ($t === '') {
            return null;
        }

        return strlen($t) > 12 ? substr($t, 0, 7) : $t;
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return list<array{name: string, ok: bool, detail: string|null}>
     */
    public static function normalizedDependencies(array $summary): array
    {
        $raw = $summary['dependencies'] ?? null;
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }
            $name = $item['name'] ?? $item['id'] ?? null;
            if (! is_string($name) || $name === '') {
                continue;
            }
            $ok = true;
            if (array_key_exists('ok', $item)) {
                $parsed = filter_var($item['ok'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $ok = $parsed !== null ? $parsed : (bool) $item['ok'];
            } elseif (array_key_exists('healthy', $item)) {
                $parsed = filter_var($item['healthy'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $ok = $parsed !== null ? $parsed : (bool) $item['healthy'];
            }
            $detail = $item['detail'] ?? $item['message'] ?? $item['error'] ?? null;
            $detail = is_string($detail) && $detail !== '' ? $detail : null;

            $out[] = ['name' => $name, 'ok' => $ok, 'detail' => $detail];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return array<string, string>
     */
    public static function normalizedLinks(array $summary): array
    {
        $raw = $summary['links'] ?? null;
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $label => $url) {
            if (! is_string($label) || $label === '' || ! is_string($url) || $url === '') {
                continue;
            }
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            $out[$label] = $url;
        }

        return $out;
    }
}
