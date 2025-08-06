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
            $table->string('blood_group')->nullable();
            $table->string('profile_image')->nullable();
            $table->tinyInteger('is_employee')->nullable()->default(0)->comment("0 => not_employee, 1 => is_employee");
            $table->string('marital_status')->nullable();
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
