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
        Schema::table('hrms_employee_documents', function (Blueprint $table) {
            $table->bigInteger('employee_id')->nullable();
            $table->bigInteger('document_title_id')->nullable();
            $table->bigInteger('sub_document_id')->nullable();
            $table->string('file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrms_employee_documents', function (Blueprint $table) {
            //
        });
    }
};
