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
        Schema::table('tree_planting_measurements', function (Blueprint $table) {
            // Fixed vocabulary ('manual'|'lidar_derived'|'lidar_assisted'),
            // enforced where it's actually set (LidarScanController), not
            // here. Defaults every existing row to 'manual' — the only
            // source that has ever existed for this table — so no backfill
            // is needed and the manual entry flow is unaffected.
            $table->string('measurement_source')->default('manual')->after('canopy_cover_pct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tree_planting_measurements', function (Blueprint $table) {
            $table->dropColumn('measurement_source');
        });
    }
};
