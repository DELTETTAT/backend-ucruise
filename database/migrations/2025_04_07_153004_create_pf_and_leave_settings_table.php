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
        Schema::create('pf_and_leave_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('pf_enabled')->default(true);
            $table->enum('pf_type', ['Percentage', 'Fixed'])->nullable();
            $table->string('pf_value')->nullable();
            $table->boolean('leave_deduction_enabled')->default(true);
            $table->json('leave_deduction')->nullable();
            $table->boolean('late_day_count_enabled')->default(false);
            $table->integer('late_day_max')->nullable();
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
        Schema::dropIfExists('pf_and_leave_settings');
    }
};
