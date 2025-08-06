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
        Schema::table('company_details', function (Blueprint $table) {
            $table->string('social_link')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->tinyInteger('time_zone_status')->default(0)->comment("0 => In active, 1 => Active");
            $table->string('language')->nullable();
            $table->string('date_formate')->nullable();
            $table->string('secondary_address')->nullable();
            $table->string('app_version')->nullable();
            $table->tinyInteger('theme_option')->default(0)->comment("0 => In active, 1 => Active");
            $table->tinyInteger('api_setting')->default(0)->comment("0 => In active, 1 => Active");
            $table->string('custom_field')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_details', function (Blueprint $table) {
            //
        });
    }
};
