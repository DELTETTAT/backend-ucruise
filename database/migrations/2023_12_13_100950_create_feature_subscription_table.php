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
        Schema::create('feature_subscription', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('subscription_id');
            // Add any additional columns you might need for the pivot table
         

            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
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
        Schema::dropIfExists('feature_subscription');
    }
};
