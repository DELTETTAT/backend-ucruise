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
        Schema::table('new_applicant', function (Blueprint $table) {
            $table->json('exists_history')->nullable();
            $table->tinyInteger('is_offered')->default(0)->comment('1 => offered, 0 => not offered');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('new_applicant', function (Blueprint $table) {
            $table->dropColumn('exists_history');
            $table->dropColumn('exists_history');
        });
    }
};
