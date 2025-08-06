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
        Schema::table('reschedules', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('feature_subscription', function (Blueprint $table) {
            $table->dropForeign(['feature_id']);
            $table->dropForeign(['subscription_id']);
        });
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['subscription_id']);
        });
        Schema::table('reschedules', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade') ; 
        });
        Schema::table('feature_subscription', function (Blueprint $table) {
            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade')->onUpdate('cascade') ;
        });
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('features')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade')->onUpdate('cascade') ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reschedules', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('feature_subscription', function (Blueprint $table) {
            $table->dropForeign(['feature_id']);
            $table->dropForeign(['subscription_id']);
        });
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['subscription_id']);
        });
    }
};
