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
        Schema::create('carers_noshow_timers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carer_id');
            $table->unsignedBigInteger('schedule_id');
            $table->string('type');
            $table->string('date');
            $table->time('start_time');
            $table->timestamps();
        });
        Schema::table('carers_noshow_timers', function (Blueprint $table) {
            $table->foreign('carer_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carers_noshow_timers');
    }
};
