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
            $table->decimal('pickup_lat', 10, 7)->nullable()->after('address');
            $table->decimal('pickup_long', 10, 7)->nullable()->after('pickup_lat');
            $table->decimal('dropoff_lat', 10, 7)->nullable()->after('drop_off_address');
            $table->decimal('dropoff_long', 10, 7)->nullable()->after('dropoff_lat');
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
            $table->dropColumn(['pickup_lat', 'pickup_long', 'dropoff_lat', 'dropoff_long']);
        });
    }
};
