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
        Schema::create('route_group_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_group_id');
            $table->string('description')->nullable();
            $table->string('user_id')->nullable();
            $table->foreign('route_group_id')->references('id')->on('route_groups')->onDelete('cascade');
            $table->timestamps();
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
        Schema::dropIfExists('route_group_users');
    }
};
