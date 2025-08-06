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
        Schema::create('ride_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('female_safety')->default(0)->nullable();
            $table->enum('noshow_frequency', ['monthly', 'weekly', 'yearly'])->nullable();
            $table->integer('noshow_count')->default(0)->nullable();
            $table->boolean('noshow')->default(0)->nullable();
            $table->time('noshow_timer')->nullable();
            $table->time('leave_timer')->nullable();
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
        Schema::dropIfExists('ride_settings');
    }
};
