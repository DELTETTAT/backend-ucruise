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
        if (!Schema::hasTable('role_sub_user')) {
            Schema::rename("role_staff", "role_sub_user");
            Schema::table('role_sub_user', function (Blueprint $table) {
                $table->renameColumn('staff_id', 'sub_user_id');
            });

        }
            Schema::table('role_sub_user', function ($table) {
                $table->foreign('sub_user_id')->references('id')->on('sub_users');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
