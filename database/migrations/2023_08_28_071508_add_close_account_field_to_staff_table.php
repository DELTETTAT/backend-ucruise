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
        Schema::table('staff', function (Blueprint $table) {
            $table->integer('close_account')->default(1)->comment('1- Active Account, 0-close Account');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('display_name')->nullable()->after('middle_name');
            $table->string('appartment_number')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('language_spoken')->nullable();
            $table->string('salutation')->nullable()->before();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn("close_account");
            $table->dropColumn("middle_name");
            $table->dropColumn("display_name");
            $table->dropColumn("appartment_number");
            $table->dropColumn("religion");
            $table->dropColumn("marital_status");
            $table->dropColumn("nationality");
            $table->dropColumn("language_spoken");
            $table->dropColumn("salutation");
        });
    }
};
