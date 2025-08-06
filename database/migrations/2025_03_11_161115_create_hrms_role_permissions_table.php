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
        Schema::create('hrms_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id')->nullable();
            $table->bigInteger('permission_id')->nullable();
            $table->boolean('can_view')->nullable()->default(0)->comment('1 => yes, 0 => no');
            $table->boolean('can_edit')->nullable()->default(0)->comment('1 => yes, 0 => no');
            $table->boolean('can_access')->nullable()->default(0)->comment('1 => yes, 0 => no');
            $table->boolean('status')->nullable()->default(1)->comment('1 => Active, 0 => Inactive');
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
        Schema::dropIfExists('hrms_role_permissions');
    }
};
