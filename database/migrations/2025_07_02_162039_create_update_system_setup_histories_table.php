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
        Schema::create('update_system_setup_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->integer('updated_by')->nullable();
            $table->text('notes')->nullable();
            $table->text('changed')->nullable();
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
        Schema::dropIfExists('update_system_setup_histories');
    }
};
