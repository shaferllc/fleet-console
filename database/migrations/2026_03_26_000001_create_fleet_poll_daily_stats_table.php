<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_poll_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->string('target_key', 64);
            $table->date('stat_date');
            $table->unsignedInteger('sample_count')->default(0);
            $table->unsignedInteger('ok_count')->default(0);
            $table->unsignedInteger('latency_p50')->nullable();
            $table->unsignedInteger('latency_p95')->nullable();
            $table->timestamp('aggregated_at')->useCurrent();

            $table->unique(['target_key', 'stat_date']);
            $table->index(['stat_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_poll_daily_stats');
    }
};
