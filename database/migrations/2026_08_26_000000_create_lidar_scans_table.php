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
        Schema::create('lidar_scans', function (Blueprint $table) {
            $table->id();

            // Nullable and nullOnDelete — a scan is supplementary evidence
            // for a measurement's numbers, not a first-class record that's
            // meaningless without one (the opposite reasoning from
            // tree_planting_measurements.tree_planting_id, which cascades).
            // A scan can be captured and uploaded before the measurement
            // record it will support is even finalized.
            $table->foreignId('tree_planting_measurement_id')
                ->nullable()
                ->constrained('tree_planting_measurements')
                ->nullOnDelete();

            // Who captured/uploaded it — mirrors
            // tree_planting_measurements.user_id.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Free string, not a DB enum — matches pictures.capture_source's
            // established convention (e.g. 'phone_lidar', 'drone', 'satellite').
            $table->string('scan_source')->nullable();

            $table->string('scan_file_path')->nullable();

            $table->decimal('lidar_height_cm', 6, 2)->nullable();
            $table->decimal('lidar_dbh_estimate_cm', 6, 2)->nullable();
            $table->decimal('lidar_canopy_area_m2', 8, 2)->nullable();

            // Fixed vocabulary ('high'|'medium'|'low'), enforced at the
            // validation layer in LidarScanController — not a DB constraint,
            // consistent with how tree_planting_measurements.canopy_cover_pct's
            // 0-100 range is enforced in the controller, not here.
            $table->string('scan_confidence')->nullable();

            $table->text('method_note')->nullable();

            $table->timestamps();

            $table->index('tree_planting_measurement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lidar_scans');
    }
};
