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
        Schema::table('ratings', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->nullable()->after('schedule_carer_id');
           $table->unsignedBigInteger('reason_id')->nullable()->after('rate');
        });
        Schema::table('ratings', function (Blueprint $table) {
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
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropColumn(['driver_id']);
            $table->dropColumn(['reason_id']); 
            $table->dropForeign(['reason_id']); 
            $table->dropForeign(['driver_id']); 
            
        });
    }
};
