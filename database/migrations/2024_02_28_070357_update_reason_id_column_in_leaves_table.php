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
       
            Schema::table('leaves', function (Blueprint $table) {
                $table->dropForeign(['reason_id']);
                $table->dropColumn(['reason_id']);
             });
             Schema::table('leaves', function (Blueprint $table) {
                 $table->unsignedBigInteger('reason_id')->nullable();
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
        Schema::table('leaves', function (Blueprint $table) {
            Schema::table('leaves', function (Blueprint $table) {
           
                $table->dropForeign(['reason_id']);
                $table->dropColumn(['reason_id']);
            });
            Schema::table('leaves', function (Blueprint $table) {
                $table->unsignedBigInteger('reason_id');
                $table->foreign('reason_id')->references('id')->on('reasons')->onDelete('cascade')->onUpdate('cascade');
             });
        });
    }
};
