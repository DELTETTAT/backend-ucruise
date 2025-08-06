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
        Schema::create('hrms_sub_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reason_id');
            $table->string('sub_categories');
            $table->timestamps();
            $table->foreign('reason_id')->references('id')->on('hrms_reasons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrms_sub_reasons');
    }
};
