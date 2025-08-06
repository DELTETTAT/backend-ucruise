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
        Schema::table('ride_settings', function (Blueprint $table) {
            //
            $table->string('schedule_bound')->nullable();
            $table->string('radius')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ride_settings', function (Blueprint $table) {
            //
            $table->dropColumn('schedule_bound');
            $table->dropColumn('radius');
        });
    }
};
