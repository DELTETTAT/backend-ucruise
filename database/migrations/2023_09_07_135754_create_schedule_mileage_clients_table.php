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
        Schema::create('schedule_mileage_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('client_id');
            $table->timestamps();
        });

        Schema::table('schedule_mileage_clients', function ($table) {
            $table->foreign('schedule_id')->references('id')->on('schedules');
            $table->foreign('client_id')->references('id')->on('users');
        });

        Schema::table('schedules', function ($table) {
            $table->integer('mileage')->after('drop_off_apartment_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_mileage_clients');
    }
};
