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
        Schema::create('salary_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('notice_period_days')->nullable()->comment('Number of days for notice period after employee resignation');
            $table->integer('salary_process_after_in_days')->nullable()->comment('Number of days after notice period completion to process salary');
            $table->integer('clear_salary')->default(0)->comment('Salary is cleared every month if set, 1 for clear, 0 for not clear');
            $table->integer('hold_one_month_salary')->default(0)->comment('0 = hold one month salary, 1 = process salary without hold');
            $table->integer('clear_salary_after_notice')->default(0)->comment('0 = Do not clear salary after notice, 1 = Clear salary after notice');
            $table->tinyInteger('salary_status_for_month_hours')->default(0)->comment('0 = Salary calculated on monthly basis, 1 = Based on working hours');
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
        Schema::dropIfExists('salary_settings');
    }
};
