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
        Schema::table('hiring_templates', function (Blueprint $table) {
            $table->tinyInteger('template_type')->default(0)->after('template_name')->comment('0: email template, 1: offered temp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiring_templates', function (Blueprint $table) {
            //
        });
    }
};
