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
        Schema::create('hrms_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hrms_team_id')->nullable();
            $table->foreign('hrms_team_id')->references('id')->on('hrms_teams')->onDelete('cascade');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->tinyInteger('status')->comment("1 => Active, 0=>In-Active")->default(1);
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
        Schema::dropIfExists('hrms_team_members');
    }
};
