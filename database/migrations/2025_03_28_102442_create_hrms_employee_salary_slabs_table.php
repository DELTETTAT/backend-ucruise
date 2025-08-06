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
        Schema::create('hrms_employee_salary_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('experience_level')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('year_experience', 3, 1)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 => Active , 0 => Inactive');
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
        Schema::dropIfExists('hrms_employee_salary_slabs');
    }
};
