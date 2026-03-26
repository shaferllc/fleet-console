<?php

namespace Tests;

use App\Models\FleetConsoleSetting;
use App\Models\FleetTarget;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function fleetSettings(): FleetConsoleSetting
    {
        return FleetConsoleSetting::query()->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function installFleetTarget(array $overrides = []): FleetTarget
    {
        return FleetTarget::query()->create(array_merge([
            'key' => 'alpha',
            'name' => 'Alpha',
            'description' => null,
            'base_url' => 'https://alpha.test',
            'site_url' => null,
            'staging_site_url' => null,
            'operator_path_prefix' => '/api/operator',
            'operator_token' => 'op-tok-32-characters-min-for-tests',
            'sort_order' => 0,
            'is_enabled' => true,
        ], $overrides));
    }
}
