<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_console_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('alert_email')->nullable();
            $table->string('alert_slack_webhook', 2048)->nullable();
            $table->boolean('alert_on_recovery')->default(false);
            $table->json('alert_metric_rules')->nullable();
            $table->decimal('alert_slo_min_ok_percent', 8, 3)->nullable();
            $table->unsignedSmallInteger('alert_slo_dedupe_hours')->default(6);
            $table->json('alert_webhook_urls')->nullable();
            $table->timestamps();
        });

        DB::table('fleet_console_settings')->insert([
            'alert_email' => null,
            'alert_slack_webhook' => null,
            'alert_on_recovery' => false,
            'alert_metric_rules' => json_encode([]),
            'alert_slo_min_ok_percent' => null,
            'alert_slo_dedupe_hours' => 6,
            'alert_webhook_urls' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_console_settings');
    }
};
