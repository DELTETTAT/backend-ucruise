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
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('pick_time')->nullable();
            $table->string('drop_time')->nullable();
            $table->tinyInteger('is_repeat')->comment('0-No, 1-Yes');
            $table->tinyInteger('shift_finishes_next_day')->comment('0-No, 1-Yes');
            $table->tinyInteger('reacurrance')->nullable()->comment('0-daily, 1-weekly, 2-monthly');
            $table->integer('repeat_time')->nullable();
            $table->string('occurs_on')->nullable();
            $table->unsignedBigInteger('pricebook_id')->nullable();
            $table->foreign('pricebook_id')->references('id')->on('price_books')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('schedule_templates');
    }
};
