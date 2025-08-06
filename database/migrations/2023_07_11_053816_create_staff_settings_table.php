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
        // Staff Settings
        Schema::create('staff_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->string('teams')->nullable();
            $table->string('notify_timesheet_approval')->nullable();
            $table->string('available_for_rostering')->nullable();
            $table->string('staff_visibleity')->nullable();
            $table->string('private_notes')->nullable();
            $table->string('no_access')->nullable();
            $table->string('account_owner')->nullable();
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('users');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_settings');
    }
};
