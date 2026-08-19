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
            $table->timestamp('captured_at')->nullable()->after('show_on_welcome');
            $table->decimal('captured_latitude', 10, 7)->nullable()->after('captured_at');
            $table->decimal('captured_longitude', 10, 7)->nullable()->after('captured_latitude');
            // 'exif' (file-upload path) or 'device_geolocation' (canvas
            // camera-capture path, which structurally cannot carry EXIF).
            $table->string('capture_source')->nullable()->after('captured_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pictures', function (Blueprint $table) {
            $table->dropColumn(['captured_at', 'captured_latitude', 'captured_longitude', 'capture_source']);
        });
    }
};
