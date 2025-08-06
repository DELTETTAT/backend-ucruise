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
        Schema::create('excel_upload_errors', function (Blueprint $table) {
            $table->id();
            $table->string('upload_id'); // Grouping purpose
            $table->unsignedInteger('batch_number');
            $table->json('errors'); // Store 50 or 100 rows at a time
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
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
        Schema::dropIfExists('excel_upload_errors');
    }
};
