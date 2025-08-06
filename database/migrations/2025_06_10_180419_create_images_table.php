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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable(); // banners, products, gallery, sliders, etc.
            $table->string('position')->nullable(); // For display ordering
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image_name')->nullable(); // Storage path for the image
            $table->string('status')->default('active'); // active/inactive
            
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
        Schema::dropIfExists('images');
    }
};
