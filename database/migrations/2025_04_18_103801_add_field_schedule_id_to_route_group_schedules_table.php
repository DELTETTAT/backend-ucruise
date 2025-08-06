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
        Schema::table('route_group_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_id')->default(0)->after('is_schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('route_group_schedules', function (Blueprint $table) {
            $table->dropColumn('schedule_id');
        });
    }
};
