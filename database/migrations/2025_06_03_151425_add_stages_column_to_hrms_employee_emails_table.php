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
        Schema::table('hrms_employee_emails', function (Blueprint $table) {
            $table->integer('stages')->nullable()->comment('0 => new applicant, 1 to 3 => in progress, 4 => offered');
            $table->string('reason')->nullable();
            $table->integer('status')->nullable()->comment('0 => New Applicant, 1 => In Progress, 2 => Future Refrence, 3 => Rejected, 4 => Offered, 5 => Re Offered, 6 => Again Re Offered');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_employee_emails', function (Blueprint $table) {
            //
        });
    }
};
