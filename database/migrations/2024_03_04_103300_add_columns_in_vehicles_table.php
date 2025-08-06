<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->nullable();
           $table->string('chasis_no')->nullable();
           $table->string('color')->nullable();
           $table->string('vehicle_no')->nullable();
           $table->string('image')->nullable();
           $table->string('registration_no')->nullable();
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade');
        });

       

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
          $table->dropForeign(['driver_id']);
          $table->dropColumn(['driver_id']);
          $table->dropColumn(['chasis_no']);
          $table->dropColumn(['color']);
          $table->dropColumn(['vehicle_no']);
          $table->dropColumn(['registration_no']);
        });
    }
};
