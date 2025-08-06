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
        Schema::create('new_applicant', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->date('dob')->nullable();
            $table->bigInteger('designation_id')->nullable();
            $table->tinyInteger('role')->nullable()->comment("1 => junior, 2 => mid, 3 => senior");
            $table->string('linkedin_url')->nullable();
            $table->string('upload_resume')->nullable();
            $table->text('cover_letter')->nullable();
            $table->text('skills')->nullable();
            $table->text('experience')->nullable();
            $table->integer('typing_speed')->nullable();
            $table->tinyInteger('is_notice')->default(0)->comment("0 => no, 1=> yes");
            $table->string('notice_period')->nullable();
            $table->date('expected_date_of_join')->nullable();
            $table->tinyInteger('working_nightshift')->default(0)->comment("0 => no, 1=> yes");
            $table->tinyInteger('cab_facility')->default(0)->comment("0 => no, 1=> yes");
            $table->tinyInteger('stages')->default(1)->comment("1 for new applicant 2 for In Progress 3 for future reference 4 for offered list 7 for rejected");
            $table->string('referral_name')->nullable();
            $table->string('employee_code')->nullable();
            $table->decimal('current_salary', 10, 2)->nullable();
            $table->decimal('salary_expectation', 10, 2)->nullable();
            $table->text('why_do_you_want_to_join_unify')->nullable();
            $table->string('how_do_you_come_to_know_about_unify')->nullable();
            $table->text('weakness')->nullable();
            $table->text('strength')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->tinyInteger('is_rejected')->default(0)->comment("0 => no, 1=> yes");
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
        Schema::dropIfExists('new_applicant');
    }
};
