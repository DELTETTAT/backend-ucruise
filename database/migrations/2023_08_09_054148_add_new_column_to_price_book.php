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
        Schema::table('price_books', function (Blueprint $table) {
            $table->string('external_id')->nullable();
            $table->string('fixed_price')->nullable();
            $table->string('provider_travel')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_books', function (Blueprint $table) {
            //
        });
    }
};
