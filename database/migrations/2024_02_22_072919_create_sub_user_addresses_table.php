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
        Schema::create('sub_user_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_user_id');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable()->nullable();
            $table->decimal('longitude', 10, 7)->nullable()->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
        Schema::table('sub_user_addresses', function (Blueprint $table) {
            $table->foreign('sub_user_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_user_addresses');
    }
};
