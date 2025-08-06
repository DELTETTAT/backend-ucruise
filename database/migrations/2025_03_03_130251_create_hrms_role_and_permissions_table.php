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
        Schema::create('hrms_role_and_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('hrms_role_permission_title_id');
            $table->foreign('hrms_role_permission_title_id')->references('id')->on('hrms_role_permission_titles')->onDelete('cascade');
            $table->json('permissions')->nullable();
            $table->tinyInteger('status')->default(1)->comment("1 => Active, 0 => Inactive");
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
        Schema::dropIfExists('hrms_role_and_permissions');
    }
};
