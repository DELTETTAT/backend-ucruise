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
            //
           // $table->dropForeign(['schedule_parent_id']);
           $table->dropForeign(['schedule_parent_id']);
           $table->dropColumn('schedule_parent_id');
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
            $table->unsignedBigInteger('schedule_parent_id')->nullable();

            // Recreate the foreign key constraint
            $table->foreign('schedule_parent_id')->references('id')->on('schedules')->onDelete('cascade');
        });
    }
};
