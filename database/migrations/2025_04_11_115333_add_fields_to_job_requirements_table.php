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
        Schema::table('job_requirements', function (Blueprint $table) {
            $table->text('qualifications')->nullable()->after('job_description');
            $table->text('benefits')->nullable()->after('qualifications');
            $table->string('start_date')->nullable()->after('benefits');
            $table->string('deadline')->nullable()->after('start_date');
            $table->string('shift_schedule')->nullable()->after('deadline');
            $table->tinyInteger('post_status')->nullable()->after('shift_schedule')->comment('0 => Not Start, 1 => In Progress, 2=> On Hold, 3=> Done');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_requirements', function (Blueprint $table) {
            $table->dropColumn('qualifications');
            $table->dropColumn('benefits');
            $table->dropColumn('start_date');
            $table->dropColumn('deadline');
            $table->dropColumn('shift_schedule');
            $table->dropColumn('post_status');
        });
    }
};
