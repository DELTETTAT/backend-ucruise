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
        Schema::create('clientsettings', function (Blueprint $table) {
            $table->id();
            $table->string('NDIS_number')->nullable();
            $table->string('recipient_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('custom_field')->nullable();
            $table->string('po_number')->nullable();
            $table->string('client_type')->nullable();
            $table->string('price_book')->nullable();
            $table->string('team')->nullable();
            $table->string('progress_note')->nullable();
            $table->string('enable_sms_reminder')->nullable();
            $table->string('invoice_travel')->nullable();
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
        Schema::dropIfExists('clientsettings');
    }
};
