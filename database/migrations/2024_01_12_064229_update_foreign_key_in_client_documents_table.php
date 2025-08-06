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
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
        });
        
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('driver_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade') ;
        });
        Schema::table('client_documents', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade') ;
        });
        
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
        });
    }
};
