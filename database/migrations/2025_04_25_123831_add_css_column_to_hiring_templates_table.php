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
        Schema::table('hiring_templates', function (Blueprint $table) {
            $table->decimal('watermarkOpacity', 3, 2)->nullable();
            $table->integer('watermarkPosition')->nullable();
            $table->integer('headerImagePosition')->nullable();
            $table->integer('footerImagePosition')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiring_templates', function (Blueprint $table) {
            //
        });
    }
};
