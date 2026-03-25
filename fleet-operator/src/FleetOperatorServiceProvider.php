<?php

namespace Dply\FleetOperator;

use Illuminate\Support\ServiceProvider;

class FleetOperatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fleet_operator.php', 'fleet_operator');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/fleet_operator.php' => config_path('fleet_operator.php'),
        ], 'fleet-operator-config');

        $this->publishes([
            __DIR__.'/../resources/openapi.yaml' => base_path('docs/fleet-operator-openapi.yaml'),
        ], 'fleet-operator-openapi');
    }
}
