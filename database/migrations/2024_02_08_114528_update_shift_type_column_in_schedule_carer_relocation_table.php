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
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
          $table->smallInteger('shift_type')->comment('1:Both, 2:pick, 3:Drop')->change();
          $table->renameColumn('address','temp_address');
          $table->renameColumn('latitude','temp_latitude');
          $table->renameColumn('longitude','temp_longitude');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_carer_relocations', function (Blueprint $table) {
            $table->string('shift_type')->change();
            $table->renameColumn('temp_address','address');
            $table->renameColumn('temp_latitude','latitude');
            $table->renameColumn('temp_longitude','longitude');
            
        });
    }
};
