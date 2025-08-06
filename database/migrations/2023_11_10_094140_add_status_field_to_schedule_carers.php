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
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('is_active')->default(1)->comment('0-Inactive, 1-Active');
            $table->timestamps();
        });
        
        Schema::create('schedule_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id')->nullable(); 
            $table->foreign('schedule_id')->references('id')->on('schedules');  
            $table->string('date');
            $table->unsignedBigInteger('status_id')->nullable(); 
            $table->foreign('status_id')->references('id')->on('statuses');  
            $table->timestamps();
        });

        Schema::create('schedule_carer_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_carer_id')->nullable(); 
            $table->foreign('schedule_carer_id')->references('id')->on('schedule_carers');  
            $table->string('date');
            $table->unsignedBigInteger('status_id')->nullable(); 
            $table->foreign('status_id')->references('id')->on('statuses');  
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
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('schedule_statuses');
        Schema::dropIfExists('schedule_carer_statuses');
    }
};
