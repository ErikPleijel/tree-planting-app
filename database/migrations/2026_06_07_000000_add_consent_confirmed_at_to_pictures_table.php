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
        Schema::table('pictures', function (Blueprint $table) {
            // Set to now() at upload time once the uploader has checked the
            // required consent-attestation checkbox. Nullable and never
            // backfilled — existing photos predate the checkbox and are
            // deliberately left null rather than retroactively gated.
            $table->timestamp('consent_confirmed_at')->nullable()->after('capture_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pictures', function (Blueprint $table) {
            $table->dropColumn('consent_confirmed_at');
        });
    }
};
