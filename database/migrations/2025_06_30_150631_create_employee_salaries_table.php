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

        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // FK to sub_users
            $table->decimal('basic', 10, 2)->default(0);
            $table->decimal('hra', 10, 2)->default(0);
            $table->decimal('medical', 10, 2)->default(0);
            $table->decimal('conveyance', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('professional_tax', 10, 2)->default(0);
            $table->decimal('epf_employee', 10, 2)->default(0);
            $table->decimal('epf_employer', 10, 2)->default(0);
            $table->decimal('esi_employee', 10, 2)->default(0);
            $table->decimal('esi_employer', 10, 2)->default(0);
            $table->decimal('take_home', 10, 2)->default(0);
            $table->decimal('total_package_salary', 10, 2)->default(0);
            $table->date('increment_from_date')->nullable();
            $table->date('increment_to_date')->nullable();
            $table->boolean('is_active')->default(0);
            $table->string('reason', 1000)->nullable();
            $table->unsignedTinyInteger('epf_type')->default(1); // 1 = Employee pays, 2 = Employer pays both, 3 = Not applicable
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('sub_users' )->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_salaries');
    }
};
