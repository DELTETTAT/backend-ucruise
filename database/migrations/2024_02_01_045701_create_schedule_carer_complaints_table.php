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
        Schema::create('schedule_carer_complaints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->string('schedule_type');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('driver_id');   
            $table->unsignedBigInteger('reason_id');   
            $table->date('date');
            $table->text('text')->nullable();
            $table->string('type')->nullable();
            $table->string('image_path')->nullable(); // Add column for image
            $table->string('audio_path')->nullable(); // Add column for audio
            $table->string('video_path')->nullable(); 
            $table->tinyInteger('status')->default(1)->comment('1-active, 0-inactive');
            $table->timestamps();
        });
        Schema::table('schedule_carer_complaints', function (Blueprint $table) {
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('staff_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('driver_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reason_id')->references('id')->on('reasons')->onDelete('cascade')->onUpdate('cascade');
        });
          

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_carer_complaints');
    }
};
