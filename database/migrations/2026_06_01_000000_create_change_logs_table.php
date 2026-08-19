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
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();

            // Polymorphic target, deliberately NOT a foreign key: history must
            // survive a hard delete of the parent row (planting_locations and
            // tree_plantings both cascade-delete today, and a constrained FK
            // here would either block that delete or cascade-delete the very
            // history this table exists to preserve).
            $table->string('loggable_type');
            $table->unsignedBigInteger('loggable_id');

            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();

            // Consistent with the existing tree_plantings.status_updated_by
            // pattern: nullable FK that nulls out (rather than cascading) if
            // the user is later deleted.
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            // Snapshot of the user's name at the time of the change, so
            // attribution survives even after changed_by nulls out.
            $table->string('changed_by_name')->nullable();

            // Append-only table: created_at only, no updated_at.
            $table->timestamp('created_at')->nullable();

            $table->index(['loggable_type', 'loggable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_logs');
    }
};
