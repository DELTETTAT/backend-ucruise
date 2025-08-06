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
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('break_time_in_minutes')->nullable()->change();
        });

        Schema::table('schedule_carers', function (Blueprint $table) {
            $table->timestamps();
        });

        Schema::table('schedule_clients', function (Blueprint $table) {
            $table->timestamps();
        });

        Schema::table('schedule_tasks', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            //
        });
    }
};
