<?php

namespace App\Providers;

use App\Support\FleetConsoleDynamicConfig;
use Fleet\IdpClient\FleetIdpCustomization;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        FleetIdpCustomization::merge(require config_path('fleet_idp_overrides.php'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            FleetConsoleDynamicConfig::syncFromDatabase();
        }
    }
}
