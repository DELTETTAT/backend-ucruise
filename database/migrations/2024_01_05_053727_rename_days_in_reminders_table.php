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
        Schema::table('reminders', function (Blueprint $table) {
            $table->renameColumn('days', 'date');
           
         
        });
        Schema::table('notifications', function (Blueprint $table) {
           
            $table->string('target');
            $table->string('type');
         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->renameColumn('date', 'days');
          
        });
        Schema::table('notifications', function (Blueprint $table) {
          
            $table->dropColumn('target');
            $table->dropColumn('type');
            
        });
    }
};
