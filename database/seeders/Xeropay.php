<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Xeropay extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   



       \DB::table('allowance_type')->insert(array(
                
                array('name' => "Expense"),
                array('name' => "Mileage/Travel"),
                array('name' => "Override payitems"),
                array('name' => "Override hours"),
                array('name' => "One-off"),
                array('name' => "Permanent"),
                 
                 
                ));  
    }
}
