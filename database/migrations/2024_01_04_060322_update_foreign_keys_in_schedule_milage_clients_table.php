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
        Schema::table('schedule_mileage_clients', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['client_id']);
           
        });
        Schema::table('schedules', function (Blueprint $table) {
            
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['vehicle_id']);
        });
        Schema::table('schedule_mileage_clients', function (Blueprint $table) {
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade') ;
        });
        Schema::table('schedules', function (Blueprint $table) {
            
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade')->onUpdate('cascade') ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_mileage_clients', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['client_id']);
           
        });
        Schema::table('schedules', function (Blueprint $table) {
            
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['vehicle_id']);
        });
    }
};
