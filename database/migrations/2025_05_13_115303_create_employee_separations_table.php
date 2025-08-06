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
        Schema::create('employee_separations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('separation_type')->nullable();
            $table->date('notice_served_date')->nullable();
            $table->date('last_working_date')->nullable();
            $table->string('reason')->nullable();
            $table->text('description_of_reason')->nullable();
            $table->string('salary_process')->nullable();
            $table->string('good_for_rehire')->nullable();
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('employee_separations');
    }
};
