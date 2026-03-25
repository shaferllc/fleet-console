<?php

namespace App\Support;

use App\Models\FleetTarget;
use Illuminate\Support\Facades\Schema;

/**
 * Merges enabled {@see FleetTarget} rows into config for the current request / command run.
 * (Service provider boot runs only once per process, so HTTP follow-up requests would miss new DB rows.)
 */
class FleetConsoleDynamicConfig
{
    public static function syncTargetsFromDatabase(): void
    {
        if (! Schema::hasTable('fleet_targets')) {
            return;
        }

        $rows = FleetTarget::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        config([
            'fleet_console.targets' => $rows
                ->map(fn (FleetTarget $t) => $t->asConfigRow())
                ->all(),
        ]);
    }
}
