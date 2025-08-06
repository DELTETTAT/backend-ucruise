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
        Schema::create('daily_schedule_carers', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('carer_id');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('pay_group_id')->nullable();
            $table->string('working_days')->nullable();
            $table->string('shift_type')->nullable();
            $table->string('temp_date')->nullable();
            $table->decimal('temp_lat', 10, 7)->nullable();
            $table->decimal('temp_long', 10, 7)->nullable();
            $table->string('temp_address')->nullable();
            $table->tinyInteger('position')->default(0);
            $table->foreign('schedule_id')->references('id')->on('daily_schedules')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('carer_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_schedule_carers');
    }
};
