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
        Schema::table('schedule_statuses', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['status_id']);
            
        });
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
            $table->dropForeign(['schedule_carer_id']);
            $table->dropForeign(['status_id']);
        });
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
        });
        Schema::table('schedule_statuses', function (Blueprint $table) {
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade') ;  
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade')->onUpdate('cascade') ; 
        });
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
            $table->foreign('schedule_carer_id')->references('id')->on('schedule_carers')->onDelete('cascade')->onUpdate('cascade') ; 
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade')->onUpdate('cascade') ; 
          
        });
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade') ; 
        });
       

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_statuses', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['status_id']);
            
        });
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
            $table->dropForeign(['schedule_carer_id']);
            $table->dropForeign(['status_id']);
        });
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
        });
        
    }
};
