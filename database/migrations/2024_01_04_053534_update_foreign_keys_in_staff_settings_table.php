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
        Schema::table('staff_settings', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_kin', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_payroll_settings', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_documents', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_settings', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')  ;
        });

        Schema::table('staff_kin', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')  ;
        });
        Schema::table('staff_payroll_settings', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')  ;
        });
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')  ;
        });
        Schema::table('staff_documents', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade')  ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_settings', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_kin', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_payroll_settings', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        Schema::table('staff_documents', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
    }
};
