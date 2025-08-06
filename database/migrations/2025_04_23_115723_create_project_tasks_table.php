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
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('min_project_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('assigned_id')->nullable();
            $table->tinyInteger('priority')->comment("0 => Low, 1 => Medium, 2=>High")->default(1);
            $table->tinyInteger('status')->comment("0=>Not Startrd, 1=>In-Progress, 2=>Completed")->default(0);
            $table->foreign('min_project_id')->references('id')->on('min_projects')->onDelete('cascade');
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
        Schema::dropIfExists('project_tasks');
    }
};
