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
        Schema::table('schedule_carers', function (Blueprint $table) {
          $table->string('temp_date')->nullable()->after('shift_type');
          $table->decimal('temp_lat', 10, 7)->nullable()->after('temp_date');
          $table->decimal('temp_long', 10, 7)->nullable()->after('temp_lat');
         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_carers', function (Blueprint $table) {
            $table->dropColumn(['temp_date']);
            $table->dropColumn(['temp_lat']);
            $table->dropColumn(['temp_long']);
        });
    }
};
