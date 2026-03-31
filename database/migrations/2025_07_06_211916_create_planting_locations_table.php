<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('planting_locations', function (Blueprint $table) {
            $table->id();

            // Public QR identifier
            $table->string('public_code', 8)->unique();

            $table->string('location');
            $table->foreignId('division_id')->constrained('division')->onDelete('cascade');
            $table->text('comment')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('status_id')->constrained('planting_location_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_locations');
    }
};
