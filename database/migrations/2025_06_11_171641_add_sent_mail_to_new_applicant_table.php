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
            //
            $table->tinyInteger('sent_mail')->default(0)->comment('0=not_sent, 1=sent_mail');
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
            //
        });
    }
};
