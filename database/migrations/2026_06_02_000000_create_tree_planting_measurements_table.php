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
        Schema::create('tree_planting_measurements', function (Blueprint $table) {
            $table->id();

            // Unlike change_logs.loggable_id (Phase 1, deliberately
            // unconstrained so audit history survives a hard delete), this
            // FK cascades on purpose: a measurement has no meaning
            // independent of the planting it measures, so it should not
            // outlive its parent.
            $table->foreignId('tree_planting_id')->constrained()->onDelete('cascade');

            $table->date('measurement_date');
            $table->unsignedInteger('trees_surviving')->nullable();
            $table->decimal('height_avg_cm', 6, 2)->nullable();
            $table->decimal('dbh_avg_cm', 6, 2)->nullable();
            $table->decimal('canopy_cover_pct', 5, 2)->nullable();
            $table->text('notes')->nullable();

            // Who recorded it.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Who verified it — deliberately separate from user_id, and
            // never the same person (enforced in the controller).
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // This table is editable (unlike change_logs), so normal
            // created_at/updated_at is correct here.
            $table->timestamps();

            $table->index('tree_planting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_planting_measurements');
    }
};
