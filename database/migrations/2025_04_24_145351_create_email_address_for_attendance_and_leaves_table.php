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
        Schema::create('email_address_for_attendance_and_leaves', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->tinyInteger('cc_and_main_type')->nullable()->comment('1 => main, 0 => cc');
            $table->tinyInteger('type')->nullable()->comment('1 => attendance, 0 => leave');
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
        Schema::dropIfExists('email_address_for_attendance_and_leaves');
    }
};
