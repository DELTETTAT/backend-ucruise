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
        Schema::create('payroll_schedules', function (Blueprint $table) {
            $table->id();
            $table->boolean('actual_days_in_month')->comment('true for Active, false for Inactive');
            $table->boolean('working_times_hours_in_month')->comment('true for Active, false for Inactive');
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
        Schema::dropIfExists('payroll_schedules');
    }
};
