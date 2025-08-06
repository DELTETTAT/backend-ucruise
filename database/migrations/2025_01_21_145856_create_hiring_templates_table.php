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
        Schema::create('hiring_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name'); // Name of the template
            $table->string('title'); // Title of the document
            $table->tinyInteger('status')->default(0)->comment("0 => inactive, 1 => active"); // Status: 0 = inactive, 1 = active
            $table->text('header_image')->nullable(); 
            $table->text('background_image')->nullable();
            $table->text('watermark')->nullable();
            $table->text('footer_image')->nullable();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_issue')->nullable();
            $table->text('content')->nullable();
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
        Schema::dropIfExists('hiring_templates');
    }
};
