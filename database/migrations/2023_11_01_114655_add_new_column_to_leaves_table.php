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
        Schema::table('leaves', function (Blueprint $table) {
          $table->date('date')->change();
          $table->tinyInteger('type')->default(1)->comment('1: Full Leave, 2: Morning Half, 3: Evening Half')->after('status');
          
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->string('date')->change();
            $table->dropColumn(['type']);
        });
    }
};
