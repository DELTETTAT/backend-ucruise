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
        Schema::table('sub_users', function (Blueprint $table) {
            $table->dropColumn(['latitude']);
            $table->dropColumn(['longitude']);
            $table->dropColumn(['address']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_users', function (Blueprint $table) {
            $table->string('latitude')->after('shift_type')->nullable();
            $table->string('longitude')->after('latitude')->nullable();
            $table->string('address')->after('longitude')->nullable();
        });
    }
};
