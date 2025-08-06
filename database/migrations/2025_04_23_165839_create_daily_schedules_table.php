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
        Schema::create('daily_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('driver_id')->constrained('sub_users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('schedule_parent_id')->nullable();
            $table->tinyInteger('shift_finishes_next_day')->default(0)->comment('0-No, 1-Yes');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('break_time_in_minutes')->nullable();
            $table->tinyInteger('is_repeat')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('is_splitted')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('reacurrance')->nullable()->comment('0-daily, 1-weekly, 2-monthly');
            $table->integer('repeat_time')->nullable();
            $table->string('occurs_on')->nullable();
            $table->string('end_date')->nullable();
            $table->string('address');
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_long', 10, 7)->nullable();
            $table->string('apartment_no')->nullable();
            $table->tinyInteger('is_drop_off_address')->default(0)->comment('0-No, 1-Yes');
            $table->string('drop_off_address')->nullable();
            $table->string('excluded_dates')->nullable();
            $table->decimal('dropoff_lat', 10, 7)->nullable();
            $table->decimal('dropoff_long', 10, 7)->nullable();
            $table->string('drop_off_apartment_no')->nullable();
            $table->integer('mileage')->nullable();
            $table->foreignId('shift_type_id')->constrained('shift_types')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('allowance_id')->nullable()->constrained('allowances')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('additional_cost')->nullable();
            $table->tinyInteger('ignore_staff_count')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('confirmation_required')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('notify_carer')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('add_to_job_board')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('shift_assignment')->nullable()->comment('0-approve automatically, 1-require approval');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('language_id')->nullable()->constrained('languages')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('compliance_id')->nullable()->constrained('report_headings')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('competency_id')->nullable()->constrained('report_headings')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('kpi_id')->nullable()->constrained('report_headings')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('distance_from_shift_location')->nullable();
            $table->string('instructions')->nullable();
            $table->string('locality')->nullable();
            $table->string('city')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->foreignId('pricebook_id')->nullable()->constrained('price_books')->onDelete('cascade')->onUpdate('cascade');
            $table->boolean('previous_day_pick')->default(0)->comment('0 means off, 1 means on');
            $table->tinyInteger('position_status')->default(1);
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
        Schema::dropIfExists('daily_schedules');
    }
};
