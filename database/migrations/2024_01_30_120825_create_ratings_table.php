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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_carer_id');
            $table->date('date');
            $table->string('rate');
            $table->string('comment');
            $table->timestamps();
        });
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreign('schedule_carer_id')->references('id')->on('schedule_carers')->onDelete('cascade')->onUpdate('cascade') ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
