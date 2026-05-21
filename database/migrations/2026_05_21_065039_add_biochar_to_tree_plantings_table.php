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
        Schema::table('tree_plantings', function (Blueprint $table) {
            $table->decimal('biochar', 4, 2)->nullable()->after('number_of_trees');
        });
    }

    public function down(): void
    {
        Schema::table('tree_plantings', function (Blueprint $table) {
            $table->dropColumn('biochar');
        });
    }
};
