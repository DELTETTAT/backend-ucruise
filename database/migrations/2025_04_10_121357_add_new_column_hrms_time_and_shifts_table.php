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
        Schema::table('hrms_time_and_shifts', function (Blueprint $table) {
            $table->tinyInteger('shift_finishs_next_day')->default(0)->comment('0 => no shift finishs next day, 1 => shift finishs next day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_time_and_shifts', function (Blueprint $table) {
            //
        });
    }
};
