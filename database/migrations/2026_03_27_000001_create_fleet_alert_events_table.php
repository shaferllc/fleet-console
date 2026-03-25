<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_alert_events', function (Blueprint $table) {
            $table->id();
            $table->string('target_key', 64)->nullable()->index();
            $table->string('type', 48);
            $table->string('subject', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('channels', 128)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_alert_events');
    }
};
