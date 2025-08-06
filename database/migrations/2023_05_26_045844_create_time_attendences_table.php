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
        Schema::create('time_attendences', function (Blueprint $table) {
            $table->id();
            $table->string('enable_unavailability')->nullable();
            $table->string('notice_preiod')->nullable();
            $table->string('location_check')->nullable();
            $table->string('attendance_threshold')->nullable();
            $table->string('auto_approve_shift')->nullable();
            $table->string('timesheet_precision')->nullable();
            $table->string('pay_rate')->nullable();
            $table->string('clockin_alert')->nullable();
            $table->string('clockin_alert_message')->nullable();
            $table->string('payroll_software')->nullable();
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
        Schema::dropIfExists('time_attendences');
    }
};
