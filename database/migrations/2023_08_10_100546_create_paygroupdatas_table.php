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
        Schema::create('paygroupdatas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paygroup_id');
            $table->string('day_of_week')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            $table->date('effective_date')->nullable();
            $table->string('Xero_pay_item')->nullable();
            $table->timestamps();

            $table->foreign('paygroup_id')->references('id')->on('paygroups')
            ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paygroupdatas');
    }
};
