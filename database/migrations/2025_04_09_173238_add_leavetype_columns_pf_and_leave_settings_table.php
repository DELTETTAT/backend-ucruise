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
        Schema::table('pf_and_leave_settings', function (Blueprint $table) {
            $table->integer('casual_leave')->nullable();
            $table->integer('medical_leave')->nullable();
            $table->integer('paid_leave')->nullable();
            $table->integer('unpaid_leave')->nullable();
            $table->integer('maternity_leave')->nullable();
            $table->integer('paternity_leave')->nullable();
            $table->integer('bereavement_leave')->nullable();
            $table->integer('wedding_leave')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pf_and_leave_settings', function (Blueprint $table) {
            //
        });
    }
};
