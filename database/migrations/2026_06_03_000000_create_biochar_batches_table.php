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
        Schema::create('biochar_batches', function (Blueprint $table) {
            $table->id();

            // Both nullable — a batch is locatable by planting_location_id
            // alone, or by a specific tree_planting_id (which implies a
            // location). Enforced in the controller: at least one must be
            // present. nullOnDelete (not cascade, unlike
            // tree_planting_measurements in Phase 2): real material that
            // was produced/applied should survive its linked planting
            // being deleted — it becomes unlinked, not gone.
            $table->foreignId('planting_location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tree_planting_id')->nullable()->constrained()->nullOnDelete();

            // Total quantity applied — NOT a per-tree rate, unlike the old
            // tree_plantings.biochar column this replaces going forward.
            $table->decimal('quantity_kg', 8, 2);
            $table->string('source');
            $table->string('batch_reference')->nullable();
            $table->date('application_date');
            $table->text('notes')->nullable();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Editable like tree_planting_measurements, not append-only
            // like change_logs.
            $table->timestamps();

            $table->index('planting_location_id');
            $table->index('tree_planting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biochar_batches');
    }
};
