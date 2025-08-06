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
            
          $table->string('cancel_message')->change();
            
        });
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('cancel_reason_id');
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
        Schema::table('schedule_carer_status', function (Blueprint $table) {
            $table->unsignedBigInteger('cancel_message')->change();
            $table->dropForeign(['cancel_reason_id']);
            $table->dropColumn(['cancel_reason_id']);
        });
    }
};
