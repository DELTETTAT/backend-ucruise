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
        Schema::create('user_answer_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('new_applicant_id')->nullable();
            $table->bigInteger('question_id')->nullable();
            $table->bigInteger('quiz_id')->nullable();
            $table->bigInteger('answer_id')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('question_type_id')->nullable();
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
        Schema::dropIfExists('user_answer_detail');
    }
};
