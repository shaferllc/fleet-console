<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fleet_targets', function (Blueprint $table): void {
            $table->boolean('mute_alerts')->default(false)->after('is_enabled');
            $table->decimal('alert_slo_min_ok_percent', 8, 3)->nullable()->after('mute_alerts');
            $table->unsignedSmallInteger('alert_slo_dedupe_hours')->nullable()->after('alert_slo_min_ok_percent');
            $table->json('alert_webhook_urls')->nullable()->after('alert_slo_dedupe_hours');
        });
    }

    public function down(): void
    {
        Schema::table('fleet_targets', function (Blueprint $table): void {
            $table->dropColumn([
                'mute_alerts',
                'alert_slo_min_ok_percent',
                'alert_slo_dedupe_hours',
                'alert_webhook_urls',
            ]);
        });
    }
};
