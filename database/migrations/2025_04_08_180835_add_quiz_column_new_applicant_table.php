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
        Schema::table('new_applicant', function (Blueprint $table) {
            $table->tinyInteger('quiz_status')->default(0)->comment('1 => submited quiz 0 => not submit quiz');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('new_applicant', function (Blueprint $table) {
            //
        });
    }
};
