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
        Schema::create('quiz_answer_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quiz_question_detail_id');
            $table->string('answer');
            $table->tinyInteger('is_correct')->default(1)->comment("0 => in_correct, 1 => correct");
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
        Schema::dropIfExists('quiz_answer_details');
    }
};
