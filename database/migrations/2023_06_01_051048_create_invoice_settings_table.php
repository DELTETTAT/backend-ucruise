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
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('abn')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('payment_return')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('email_message')->nullable();
            $table->string('payment_rounding')->nullable();
            $table->string('provider_number')->nullable();
            $table->string('cost_calcculation')->nullable();
            $table->string('cancelled_by_client')->nullable();
            $table->text('client_message')->nullable();
            $table->text('invoice_item_default_format')->nullable();
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
        Schema::dropIfExists('invoice_settings');
    }
};
