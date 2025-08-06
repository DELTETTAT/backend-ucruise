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
        Schema::table('employee_team_managers', function (Blueprint $table) {
            $table->tinyInteger('team_attendance_access')->nullable()->after('employee_id')->comment('1 => Permission, 0 => Not permision');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_team_managers', function (Blueprint $table) {
            $table->dropColumn('team_attendance_access');
        });
    }
};
