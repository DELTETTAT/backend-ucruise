<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayrollSchedule;

class PayrollScheduleSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'actual_days_in_month' => true,
            'working_times_hours_in_month' => false,
       ];


       PayrollSchedule::firstOrCreate($data);
    }
}
