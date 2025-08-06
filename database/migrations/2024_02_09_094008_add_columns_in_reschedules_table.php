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
        Schema::table('reschedules', function (Blueprint $table) {
           $table->date('date')->after('user_id');
           $table->unsignedBigInteger('reason_id')->nullable()->after('status');
           $table->string('text')->after('reason_id')->nullable();
        });
        Schema::table('reschedules', function (Blueprint $table) {
            $table->foreign('reason_id')->references('id')->on('reasons')->onDelete('cascade')->onUpdate('cascade');
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reschedules', function (Blueprint $table) {
            $table->dropForeign(['reason_id']);
            $table->dropColumn(['date','reason_id', 'text']);
        });
    }
};
