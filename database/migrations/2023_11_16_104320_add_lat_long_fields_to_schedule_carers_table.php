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
        Schema::table('schedule_carers', function (Blueprint $table) {
            $table->string('latitude')->after('shift_type')->nullable();
            $table->string('longitude')->after('latitude')->nullable();
            $table->tinyInteger('shift_change_request_status')->after('longitude')->default(0)->comment('0-No, 1-Yes');
        });
    }

    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_carers', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('shift_change_request_status');
        });
    }
};
