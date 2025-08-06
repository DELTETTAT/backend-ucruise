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
        Schema::table('hiring_templates', function (Blueprint $table) {
           $table->json('icon_positions')->nullable()->after('watermark'); 
            $table->json('icon_files')->nullable()->after('icon_positions'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiring_templates', function (Blueprint $table) {
              $table->dropColumn('icon_positions');
              $table->dropColumn('icon_files');
        });
    }
};
