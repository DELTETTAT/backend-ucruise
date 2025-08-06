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
        Schema::table('user_infos', function (Blueprint $table) {
            //
            $table->integer('clear_salary')->nullable()->comment('every month paid salary');
            $table->integer('hold_one_month_salary')->default(0)->comment('0 = on hold, 1 = process');
            $table->integer('clear_salary_after_notice')->default(0)->comment('0 = no, 1 = yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_infos', function (Blueprint $table) {
            //
            $table->dropColumn(['clear_salary', 'hold_one_month_salary', 'clear_salary_after_notice']);
        });
    }
};
