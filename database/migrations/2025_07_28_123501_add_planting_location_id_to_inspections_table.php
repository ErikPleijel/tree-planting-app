<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->foreignId('planting_location_id')->constrained()->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['planting_location_id']);
            $table->dropColumn('planting_location_id');
        });
    }
};
