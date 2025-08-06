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
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->date('date')->nullable();
            $table->time('login_time')->nullable();
            $table->time('logout_time')->nullable();
            $table->time('ideal_time')->nullable()->default('00:00:00');
            $table->time('production')->nullable()->default('00:00:00');
            $table->time('break')->nullable()->default('00:00:00');
            $table->time('overtime')->nullable()->default('00:00:00');
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
        Schema::dropIfExists('employee_attendances');
    }
};
