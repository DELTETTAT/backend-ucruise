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
        Schema::create('update_applicant_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('applicant_id');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->json('changed_fields')->nullable();
            $table->integer('updated_by')->nullable();
            $table->text('notes')->nullable();
            $table->text('changed')->nullable();
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
        Schema::dropIfExists('update_applicant_histories');
    }
};
