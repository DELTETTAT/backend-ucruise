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
        Schema::create('route_group_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_group_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable(false);
            $table->string('pick_time')->nullable();
            $table->string('drop_time')->nullable();
            $table->tinyInteger('shift_finishes_next_day')->default(0)->comment('0-No, 1-Yes');

            $table->tinyInteger('custom_checked')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('infinite_checked')->default(1)->comment('0-No, 1-Yes');

            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();

            $table->unsignedBigInteger('shift_type_id');  // Drop and pick

            $table->string('scheduleLocation')->nullable();
            $table->string('scheduleCity')->nullable();
            $table->string('selectedLocationLat')->nullable();
            $table->string('selectedLocationLng')->nullable();
            $table->string('pricebook_id')->nullable();
            $table->string('is_repeat')->nullable();
           // $table->string('carers')->nullable();
            $table->json('carers')->nullable();
            $table->string('repeat')->nullable();
            $table->string('seats')->nullable();
            $table->string('reacurrance')->default('weekly');
            $table->string('end_date')->default('2031-01-01');
            $table->string('repeat_weeks')->default('1');
            $table->json('occurs_on')->nullable();
            
            // $table->string('mon')->default('1');
            // $table->string('tue')->default('1');
            // $table->string('wed')->default('1');
            // $table->string('thu')->default('1');
            // $table->string('fri')->default('1');

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
        Schema::dropIfExists('route_group_schedules');
    }
};
