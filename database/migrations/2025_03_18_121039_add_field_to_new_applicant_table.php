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
            $table->tinyInteger('is_accept')->nullable()->default(null)->comment('1 = Accepted, 0 = Declined');
            $table->tinyInteger('is_feature_reference')->default(0)->comment('1 = feature, 0 = Not feature');
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
            $table->dropColumn('is_accept');
            $table->dropColumn('is_feature_reference');
        });
    }
};
