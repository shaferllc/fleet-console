<?php

namespace App\Support;

use App\Models\FleetTarget;

final class FleetDashboardTargetMeta
{
    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function attach(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $ids = FleetTarget::query()->pluck('id', 'key');

        return array_map(static function (array $row) use ($ids): array {
            $key = $row['key'] ?? null;
            if (! is_string($key) || $key === '' || ! isset($ids[$key])) {
                return $row;
            }

            $id = (int) $ids[$key];
            $row['fleet_target_id'] = $id;
            $row['edit_url'] = route('console.targets.edit', ['fleet_target' => $id]);

            return $row;
        }, $rows);
    }
}
