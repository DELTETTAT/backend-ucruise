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
      
        Schema::table('schedule_carers', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('shift_change_request_status');
        });
   
        Schema::create('schedule_carer_relocations', function (Blueprint $table) {
            $table->id();
        
            // Foreign key for schedule_id
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->foreign('schedule_id')->references('id')->on('schedules');
        
            // Other columns
            $table->unsignedBigInteger('staff_id');
            $table->date('date');
            //$table->foreign('staff_id')->references('id')->on('staffs');
        
            
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0-No, 1-Pending, 2-Accepted');
        
            // Timestamps
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
        Schema::dropIfExists('schedule_carer_relocations');
    }
};
