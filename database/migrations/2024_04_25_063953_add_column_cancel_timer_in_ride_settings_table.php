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
        Schema::table('ride_settings', function (Blueprint $table) {
           $table->string('cancel_timer')->nullable();
           $table->string('leave_timer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ride_settings', function (Blueprint $table) {
            $table->dropColumn(['cancel_timer']);
            $table->time('leave_timer')->nullable()->change();
        });
    }
};
