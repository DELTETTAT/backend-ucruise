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
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
             $table->bigInteger('carer_id')->nullable()->after('schedule_carer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_carer_statuses', function (Blueprint $table) {
             $table->dropColumn('carer_id');
        });
    }
};
