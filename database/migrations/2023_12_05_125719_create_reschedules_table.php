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
        Schema::create('reschedules', function (Blueprint $table) {
        
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->tinyInteger('status')->comment('0-Pending, 1-Approved, 2-Rejected, 3-Submitted, 4-Waiting')->nullable();
            $table->timestamps();
        });

        Schema::table('reschedules', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('sub_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reschedules');
    }
};
