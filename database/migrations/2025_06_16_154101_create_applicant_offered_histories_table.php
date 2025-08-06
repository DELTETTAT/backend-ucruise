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
        Schema::create('applicant_offered_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('applicant_id')->unsigned()->index();
            $table->string('unique_id')->nullable()->comment('Unique ID for the applicant');
            $table->date('date')->nullable();
            $table->string('offered_salary')->nullable();
            $table->date('joining_date')->nullable();
            $table->time('joining_time')->nullable();
            $table->integer('is_accept')->default(0)->comment('0 = Pending, 1 = Yes, 2 = No');
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
        Schema::dropIfExists('applicant_offered_histories');
    }
};
