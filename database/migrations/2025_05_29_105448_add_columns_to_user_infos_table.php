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
            $table->string('new_emp_code')->nullable();
            $table->string('official_name')->nullable();
            $table->string('employee_real_name')->nullable();
            $table->string('official_email')->nullable();
            $table->string('LOB')->nullable();
            $table->string('id_card_receive')->nullable();
            $table->string('appointment_letter_receive')->nullable();
            $table->string('BOND_NDA_signed_or_not')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('passport')->nullable();
            $table->string('DL')->nullable();
            $table->string('recovery_amount_pending')->nullable();
            $table->string('form11')->nullable();
            $table->string('antipochy_policy')->nullable();
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
        });
    }
};
