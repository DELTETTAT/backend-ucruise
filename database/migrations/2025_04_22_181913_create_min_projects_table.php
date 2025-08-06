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
        Schema::create('min_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('sub_project_id')->nullable();
            $table->bigInteger('assigned_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('priority')->comment('0 => low, 1 => medium, 2 => high')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 => Not Started, 1 => In Progress, 2 => Done');
            $table->integer('completion')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->foreign('sub_project_id')->references('id')->on('sub_projects')->onDelete('cascade');
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
        Schema::dropIfExists('min_projects');
    }
};
