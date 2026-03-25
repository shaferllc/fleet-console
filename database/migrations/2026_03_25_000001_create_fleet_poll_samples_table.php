<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_poll_samples', function (Blueprint $table) {
            $table->id();
            $table->string('target_key', 64)->index();
            $table->boolean('ok');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->json('summary_snapshot')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_poll_samples');
    }
};
