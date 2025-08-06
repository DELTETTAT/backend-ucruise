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
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
           $table->dropForeign(['cancel_reason_id']);
           $table->dropColumn(['cancel_reason_id']);
        });
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('cancel_reason_id')->nullable();
            $table->foreign('cancel_reason_id')->references('id')->on('reasons')->onDelete('cascade')->onUpdate('cascade');
            
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
            Schema::table('schedule_carer_statuses', function (Blueprint $table) {
                $table->dropForeign(['cancel_reason_id']);
                $table->dropColumn(['cancel_reason_id']);
             });
             Schema::table('schedule_carer_statuses', function (Blueprint $table) {
                 $table->unsignedBigInteger('cancel_reason_id');
                 $table->foreign('cancel_reason_id')->references('id')->on('reasons')->onDelete('cascade')->onUpdate('cascade');
                 
              });
        });
    }
};
