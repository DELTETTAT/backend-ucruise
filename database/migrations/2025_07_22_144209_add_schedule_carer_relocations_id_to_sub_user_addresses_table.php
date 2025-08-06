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
        Schema::table('sub_user_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_carer_relocations_id')->nullable()->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_user_addresses', function (Blueprint $table) {
            $table->dropColumn('schedule_carer_relocations_id');
        });
    }
};
