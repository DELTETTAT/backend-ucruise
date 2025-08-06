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
        Schema::create('hrms_teams', function (Blueprint $table) {
            //
            $table->id();
            $table->string('team_name')->nullable();
            $table->text('description')->nullable();
            $table->json('members')->nullable();
            $table->tinyInteger('status')->comment("0 => Inactive, 1 => Active")->default(1);
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
            //
            Schema::dropIfExists('hrms_teams');
    }
};
