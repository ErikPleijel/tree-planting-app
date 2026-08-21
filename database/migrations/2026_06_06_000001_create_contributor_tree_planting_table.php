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
        // Named to match Laravel's default belongsToMany pivot-table
        // convention for Contributor <-> TreePlanting (alphabetical
        // singular pair), so no explicit ->table() call is needed on
        // either relation.
        Schema::create('contributor_tree_planting', function (Blueprint $table) {
            // Real auto-increment id, not just a composite key — needed
            // so a specific attachment can be referenced/detached
            // directly.
            $table->id();

            // RESTRICT (the default referential action, made explicit
            // here): a Contributor cannot be deleted while any
            // attachment references it — same precedent as
            // TreeType/tree_type_id in tree_plantings.
            $table->foreignId('contributor_id')->constrained()->restrictOnDelete();

            // CASCADE: an attachment has no meaning independent of the
            // planting event it's attached to — same reasoning as
            // tree_planting_measurements' cascade (Phase 2). This pivot
            // sits at yet another point on the delete-behavior spectrum
            // established across phases: unconstrained (change_logs) /
            // cascade (tree_planting_measurements) / nullOnDelete
            // (biochar_batches) / cascade-on-one-side-restrict-on-the-
            // other (this pivot) — see DECISIONS.md.
            $table->foreignId('tree_planting_id')->constrained()->cascadeOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();

            // Duplicate-attachment prevention enforced at the DB level,
            // not just in application validation.
            $table->unique(['contributor_id', 'tree_planting_id']);

            // The composite unique index above serves contributor_id-led
            // lookups; tree_planting_id-led lookups ("who's attached to
            // this planting") — the more common query here — need their
            // own index.
            $table->index('tree_planting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributor_tree_planting');
    }
};
