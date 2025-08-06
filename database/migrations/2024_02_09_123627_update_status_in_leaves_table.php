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
           $table->smallInteger('status')->comment('0:submitted, 1:accepted, 2:rejected')->default(0)->change();
        });
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
            $table->dropColumn(['status']);
            
         });
         Schema::table('reschedules', function (Blueprint $table) {
            $table->dropColumn(['status']);
           
         });
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
           
            $table->smallInteger('status')->comment('0:submitted, 1:accepted, 2:rejected')->default(0);
         });
         Schema::table('reschedules', function (Blueprint $table) {
 
            $table->smallInteger('status')->comment('0:submitted, 1:accepted, 2:rejected')->default(0);
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
          $table->string('status')->change();
        });
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
            $table->smallInteger('status')->default(0)->comment('0-No, 1-Pending, 2-Accepted')->change();
         });
         Schema::table('reschedules', function (Blueprint $table) {
            $table->smallInteger('status')->comment('0-Pending, 1-Approved, 2-Rejected, 3-Submitted, 4-Waiting')->nullable()->change();
         });

        
        
    }
};
