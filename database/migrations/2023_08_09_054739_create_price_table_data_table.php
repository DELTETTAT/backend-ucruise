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
        Schema::create('price_table_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_book_id');
            $table->string('day_of_week')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('per_hour')->nullable();
            //$table->string('per_hour')->nullable();
            $table->string('refrence_no_hr')->nullable();
            $table->string('per_km')->nullable();
            $table->string('refrence_no')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('multiplier')->nullable();
            $table->timestamps();

            $table->foreign('price_book_id')->references('id')->on('price_books')
            ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_table_data');
    }
};
