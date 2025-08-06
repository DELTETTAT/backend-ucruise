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
        Schema::create('hrms_projects', function (Blueprint $table) {
            $table->id();
            $table->text('project_title')->nullable();
            $table->longText('description')->nullable();
            $table->bigInteger('admin_id')->nullable();
            $table->tinyInteger('status')->comment("0=>To Do, 1=>In-Progress, 2=>Completed")->default(0);
            $table->tinyInteger('priority')->comment("0 => Low, 1 => Medium, 2=>High")->default(1);
            $table->string('completed')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
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
        Schema::dropIfExists('hrms_projects');
    }
};
