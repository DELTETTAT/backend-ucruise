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
        Schema::create('tax_information', function (Blueprint $table) {
            $table->id();
            $table->string('pan', 10)->nullable();
            $table->string('tan', 10)->nullable();
            $table->string('tds_circle_code')->nullable();
            $table->enum('tax_payment_frequency', ['Monthly', 'Quarterly', 'Yearly'])->default("Monthly");
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
        Schema::dropIfExists('tax_information');
    }
};
