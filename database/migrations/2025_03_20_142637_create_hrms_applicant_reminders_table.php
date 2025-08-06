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
        Schema::create('hrms_applicant_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('new_applicant_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable()->comment("reminder date to send mail");
            $table->string('type')->nullable();
            $table->string('hiring_template_id')->nullable();
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
        Schema::dropIfExists('hrms_applicant_reminders');
    }
};
