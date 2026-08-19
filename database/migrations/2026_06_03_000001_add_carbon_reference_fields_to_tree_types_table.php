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
        Schema::table('tree_types', function (Blueprint $table) {
            $table->decimal('wood_density_kg_m3', 6, 2)->nullable()->after('description');
            $table->decimal('carbon_fraction', 4, 3)->nullable()->after('wood_density_kg_m3');
            // Nullable at the DB level so existing species with neither
            // value yet stay valid — "required whenever either numeric
            // field is populated" is enforced in TreeTypeController, not here.
            $table->text('source_reference')->nullable()->after('carbon_fraction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tree_types', function (Blueprint $table) {
            $table->dropColumn(['wood_density_kg_m3', 'carbon_fraction', 'source_reference']);
        });
    }
};
