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
        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('new_applicant_id')->nullable();
            $table->string('parmanent_address')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('assign_pc')->nullable();
            $table->string('sallary')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('spouse_name')->nullable();
            $table->integer('no_of_childern')->nullable();
            $table->date('documented_birthday')->nullable();
            $table->string('qualification')->nullable();
            $table->string('induction_status')->nullable();
            $table->string('reporting_leader')->nullable();
            $table->bigInteger('reporting_leader_id')->nullable();
            $table->string('interview_souce')->nullable();
            $table->string('referal_by')->nullable();
            $table->string('aadhar_card_number')->nullable();
            $table->string('PAN_card_number')->nullable();
            $table->string('voter_id')->nullable();
            $table->string('driving_lincense')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('IFSC_code')->nullable();
            $table->string('reason')->nullable();
            $table->string('remark')->nullable();
            $table->string('PF_status')->nullable();
            $table->string('PF_no')->nullable();
            $table->string('relieving_letter')->nullable();
            $table->string('FNF')->nullable();
            $table->string('UAN_no')->nullable();
            $table->string('assets')->nullable();
            $table->string('recovery')->nullable();
            $table->string('genious_employee_code')->nullable();
            $table->string('salary_cycle')->nullable();
            $table->string('age_in_year')->nullable();
            $table->string('skills')->nullable();
            $table->string('experience')->nullable();
            $table->string('company_email')->nullable();
            $table->string('department')->nullable();
            $table->string('drug_policy')->nullable();
            $table->string('transport_policy')->nullable();
            $table->string('laptop_phone_policy')->nullable();
            $table->string('IJP_policy')->nullable();
            $table->string('appraisal_policy')->nullable();
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
        Schema::dropIfExists('user_infos');
    }
};
