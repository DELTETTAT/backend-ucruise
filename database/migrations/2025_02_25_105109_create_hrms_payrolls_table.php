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
        Schema::create('hrms_payrolls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->decimal('total_paid_days', 4, 1)->nullable();
            $table->decimal('count_of_persent', 4, 1)->nullable();
            $table->date('date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 => approved, 2 => completed, 3 => pending');
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
        Schema::dropIfExists('hrms_payrolls');
    }
};
