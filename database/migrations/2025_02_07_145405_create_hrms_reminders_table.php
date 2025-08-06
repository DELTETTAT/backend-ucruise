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
        Schema::create('hrms_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('target')->nullable();
            $table->string('title');
            $table->text('description');
            $table->date('date')->nullable();
            $table->string('type')->nullable();
            $table->tinyInteger('status')->default(0)->comment("0 => Pending, 1 => Completed");
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
        Schema::dropIfExists('hrms_reminders');
    }
};
