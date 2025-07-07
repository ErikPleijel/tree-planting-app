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
        Schema::create('tree_plantings', function (Blueprint $table) {
            $table->id();
            $table->date('planting_date');
            $table->integer('number_of_trees');
            $table->foreignId('tree_type_id')->constrained('tree_types');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('planting_location_id')->constrained('planting_locations')->onDelete('cascade');
            $table->foreignId('status')->constrained('tree_planting_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_plantings');
    }
};
