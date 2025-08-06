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
        Schema::create('employee_team_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_manager_id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('team_manager_id')->references('id')->on('team_managers')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('sub_users');
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
        Schema::dropIfExists('employee_team_managers');
    }
};
