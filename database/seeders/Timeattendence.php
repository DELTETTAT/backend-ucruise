<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Timeattendence extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('time_attendences')->insert(array(
            array(
            'notice_preiod' => 3,
            'attendance_threshold' => 10,
            'timesheet_precision' => '1 decimal',
            'pay_rate' => 'End Time',
            'pay_rate' => 'End Time',
            'clockin_alert_message' => 'If you are feeling unwell or have any covid symptoms please contact your supervisor.',
            ),
            ));
    }
}
