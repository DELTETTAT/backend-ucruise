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
        Schema::create('payrun_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); 
            $table->date('payroll_from')->nullable();
            $table->date('payroll_to')->nullable();
            $table->date('paid_date')->nullable();
            $table->boolean('status')->default(0);
            $table->string('message', 1000)->nullable();
            $table->foreign('employee_id')->references('id')->on('sub_users' )->onDelete('cascade');
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
        Schema::dropIfExists('payrun_histories');
    }
};
