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




            $table->dropForeign(['shift_type_id']);
            $table->dropForeign(['allowance_id']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['language_id']);
            $table->dropForeign(['compliance_id']);
            $table->dropForeign(['competency_id']);
            $table->dropForeign(['kpi_id']);
        });

        Schema::table('schedule_clients', function (Blueprint $table) {



            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['client_id']);
            $table->dropForeign(['pricebook_id']);
        });

        Schema::table('schedule_carers', function (Blueprint $table) {



            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['carer_id']);
        });

        Schema::table('schedule_tasks', function (Blueprint $table) {



            $table->dropForeign(['schedule_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {



            $table->foreign('shift_type_id')->references('id')->on('shift_types')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('allowance_id')->references('id')->on('allowances')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('compliance_id')->references('id')->on('report_headings')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('competency_id')->references('id')->on('report_headings')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('kpi_id')->references('id')->on('report_headings')->onDelete('cascade')->onUpdate('cascade') ;
        });

        Schema::table('schedule_clients', function (Blueprint $table) {



            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('pricebook_id')->references('id')->on('price_books')->onDelete('cascade')->onUpdate('cascade') ;
        });

        Schema::table('schedule_carers', function (Blueprint $table) {



            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade') ;
            $table->foreign('carer_id')->references('id')->on('sub_users')->onDelete('cascade')->onUpdate('cascade') ;
        });

        Schema::table('schedule_tasks', function (Blueprint $table) {



            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade')->onUpdate('cascade') ;
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




            $table->dropForeign(['shift_type_id']);
            $table->dropForeign(['allowance_id']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['language_id']);
            $table->dropForeign(['compliance_id']);
            $table->dropForeign(['competency_id']);
            $table->dropForeign(['kpi_id']);
        });

        Schema::table('schedule_clients', function (Blueprint $table) {



            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['client_id']);
            $table->dropForeign(['pricebook_id']);
        });

        Schema::table('schedule_carers', function (Blueprint $table) {



            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['carer_id']);
        });

        Schema::table('schedule_tasks', function (Blueprint $table) {



            $table->dropForeign(['schedule_id']);
        });
    }
};
