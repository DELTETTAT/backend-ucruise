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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable(false);
            $table->tinyInteger('shift_finishes_next_day')->default(0)->comment('0-No, 1-Yes');
            $table->datetime('start_time')->nullable(false);
            $table->datetime('end_time')->nullable(false);
            $table->integer('break_time_in_minutes');
            $table->tinyInteger('is_repeat')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('reacurrance')->nullable()->comment('0-daily, 1-weekly, 2-monthly');
            $table->integer('repeat_time')->nullable();
            $table->string('occurs_on')->nullable();
            $table->date('end_date')->nullable(false);
            $table->string('address')->nullable(false);
            $table->string('apartment_no')->nullable();
            $table->tinyInteger('is_drop_off_address')->default(0)->comment('0-No, 1-Yes');
            $table->string('drop_off_address')->nullable();
            $table->string('drop_off_apartment_no')->nullable();
            $table->unsignedBigInteger('shift_type_id');
            $table->unsignedBigInteger('allowance_id');
            $table->integer('additional_cost')->nullable();
            $table->tinyInteger('ignore_staff_count')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('confirmation_required')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('notify_carer')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('add_to_job_board')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('shift_assignment')->nullable()->comment('0-approve automatically, 1-require approval');
            $table->unsignedBigInteger('team_id')->nullable();;
            $table->unsignedBigInteger('language_id')->nullable();;
            $table->unsignedBigInteger('compliance_id')->nullable();;
            $table->unsignedBigInteger('competency_id')->nullable();;
            $table->unsignedBigInteger('kpi_id')->nullable();;
            $table->integer('distance_from_shift_location')->nullable();
            $table->string('instructions')->nullable();
            $table->timestamps();
        });

        Schema::table('schedules', function ($table) {
            $table->foreign('shift_type_id')->references('id')->on('shift_types');
            $table->foreign('allowance_id')->references('id')->on('allowances');
            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('compliance_id')->references('id')->on('report_headings');
            $table->foreign('competency_id')->references('id')->on('report_headings');
            $table->foreign('kpi_id')->references('id')->on('report_headings');
        });

        Schema::create('schedule_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('client_id');
            $table->datetime('start_time')->nullable(false);
            $table->datetime('end_time')->nullable(false);
            $table->unsignedBigInteger('pricebook_id');
            $table->string('multiplier')->nullable(false);
        });

        Schema::table('schedule_clients', function ($table) {
            $table->foreign('schedule_id')->references('id')->on('schedules');
            $table->foreign('client_id')->references('id')->on('users');
            $table->foreign('pricebook_id')->references('id')->on('price_books');
        });

        Schema::create('schedule_carers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('carer_id');
            $table->datetime('start_time')->nullable(false);
            $table->datetime('end_time')->nullable(false);
            $table->unsignedBigInteger('pay_group_id');
        });

        Schema::table('schedule_carers', function ($table) {
            $table->foreign('schedule_id')->references('id')->on('schedules');
            $table->foreign('carer_id')->references('id')->on('staffs');
            //$table->foreign('pay_group_id')->references('id')->on('paygroups');
        });

        Schema::create('schedule_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('schedule_id');
            $table->string('name')->nullable(false);
            $table->tinyInteger('is_mandatory')->default(0)->comment('0-No, 1-Yes');
        });

        Schema::table('schedule_tasks', function ($table) {
            $table->foreign('schedule_id')->references('id')->on('schedules');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('schedule_clients');
        Schema::dropIfExists('schedule_carers');
        Schema::dropIfExists('schedule_tasks');
    }
};
