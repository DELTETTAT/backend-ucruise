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
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('excluded_dates')->nullable()->after('drop_off_address');
            $table->unsignedBigInteger('schedule_parent_id')->nullable()->after('vehicle_id');
            $table->foreign('schedule_parent_id')->references('id')->on('schedules')
            ->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            
                $table->dropColumn(['excluded_dates', 'schedule_parent_id']);
                
        });
    }
};
