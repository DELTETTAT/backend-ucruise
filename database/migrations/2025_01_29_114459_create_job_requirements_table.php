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
        Schema::create('job_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->bigInteger('designation_id')->nullable();
            $table->tinyInteger('roles')->nullable()->comment('1 => junior, 2 => medium, 3 => senior');
            $table->tinyInteger('job_type')->nullable()->comment('1 => intern, 2 => part_time, 3 => full_time');
            $table->tinyInteger('no_of_required_emp')->nullable();
            $table->tinyInteger('gender')->nullable()->comment('1 => male, 2 => female');;
            $table->tinyInteger('priority')->nullable()->comment('1 => low, 2 => medium, 3 => high');
            $table->tinyInteger('status')->nullable()->comment('0 => Inactive, 1 => Active');
            $table->text('job_description')->nullable();
            $table->text('justify_need')->nullable();
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
        Schema::dropIfExists('job_requirements');
    }
};
