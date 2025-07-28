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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone')->nullable();
           // $table->unsignedInteger('role_id')->default(1);
            $table->string('gender')->nullable();
          //  $table->foreignId('picture_id')->nullable()->constrained('pictures')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['picture_id']);
            $table->dropColumn(['telephone',  'gender', 'picture_id']);
        });
    }
};
