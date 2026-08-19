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
        Schema::table('planting_locations', function (Blueprint $table) {
            // Plain JSON, not a native spatial type — see the Phase 5
            // DECISIONS.md entry for the full platform-decision reasoning.
            // A single GeoJSON Polygon (one outer ring, no holes/multi-
            // polygons in this phase).
            $table->json('boundary_geojson')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planting_locations', function (Blueprint $table) {
            $table->dropColumn('boundary_geojson');
        });
    }
};
