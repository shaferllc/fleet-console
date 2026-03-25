<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fleet_targets', function (Blueprint $table) {
            $table->string('staging_site_url', 512)->nullable()->after('site_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_targets', function (Blueprint $table) {
            $table->dropColumn('staging_site_url');
        });
    }
};
