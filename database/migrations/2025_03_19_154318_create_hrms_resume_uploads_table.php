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
        Schema::create('hrms_resume_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->string('resume_name')->nullable();
            $table->tinyInteger('is_accept')->nullable()->default(null)->comment('1 = Accepted, 0 = Declined');
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
        Schema::dropIfExists('hrms_resume_uploads');
    }
};
