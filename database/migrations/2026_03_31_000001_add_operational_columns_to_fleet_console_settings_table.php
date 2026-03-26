<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fleet_console_settings', function (Blueprint $table): void {
            $table->boolean('http_verify')->default(true)->after('alert_webhook_urls');
            $table->unsignedInteger('daily_rollup_sparkline_after_samples')->default(800)->after('http_verify');
            $table->text('api_token')->nullable()->after('daily_rollup_sparkline_after_samples');
            $table->text('trusted_ips')->nullable()->after('api_token');
            $table->string('health_token')->nullable()->after('trusted_ips');
            $table->boolean('background_poll_enabled')->default(false)->after('health_token');
            $table->unsignedSmallInteger('poll_interval_minutes')->default(10)->after('background_poll_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('fleet_console_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'http_verify',
                'daily_rollup_sparkline_after_samples',
                'api_token',
                'trusted_ips',
                'health_token',
                'background_poll_enabled',
                'poll_interval_minutes',
            ]);
        });
    }
};
