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
        Schema::create('tds_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tds_from')->nullable();
            $table->string('tds_to')->nullable();
            $table->enum('tds_type', ['Percentage', 'Fixed'])->nullable();
            $table->string('tds_value')->nullable();
            $table->tinyInteger('tds_enabled')->default(1)->comment('1 for enabled, 0 for disabled');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tds_settings');
    }
};
