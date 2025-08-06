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
        Schema::create('hrms_calender_attendances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->date('date')->nullable();
            $table->enum('status', ['present', 'absent', 'halfday', 'unpaidleave', 'unpaidhalfday', 'companyholiday', 'paidleave', 'medicalleave', 'EC', 'ABS'])->default('present');
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
        Schema::dropIfExists('hrms_calender_attendances');
    }
};
