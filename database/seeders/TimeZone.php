<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeZone extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('timezones')->insert(array(
            array(
            'timezone' => "International Date Line West"
            ),
            array(
                'timezone' => "American Samoa"
                ),
            array(
                'timezone' => "Midway Island"
                ),
            array(
                    'timezone' => "Hawaii"
                    ),
            array(
                    'timezone' => "Alaska"
                    ),
             array(
                    'timezone' => "Pacific Time (US & Canada)"
                    ),
            array('timezone' => "Tijuana"),
            array('timezone' => "Arizona"),
            array('timezone' => "Mazatlan"),
            array('timezone' => "Mountain Time (US & Canada)"),
            array('timezone' => "Central Time (US & Canada)"),
             
            ));
    }
}
