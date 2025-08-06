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
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('locality')->nullable()->after('instructions');
            $table->string('city')->nullable()->after('locality');
            $table->string('latitude')->nullable()->after('city');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['locality']);
            $table->dropColumn(['city']);
            $table->dropColumn(['latitude']);
            $table->dropColumn(['longitude']);
        });
    }
};
