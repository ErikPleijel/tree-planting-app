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
            $table->foreignId('status_updated_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tree_plantings', function (Blueprint $table) {
            $table->dropForeign(['status_updated_by']);
            $table->dropColumn('status_updated_by');
        });
    }
};
