<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class Report_heading extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        $data = [
            [ 'category_name' => 'Compliance' ],
            [ 'category_name' => 'KPI' ],
            [ 'category_name' => 'Other' ],
             
            // Add more entries as needed
        ];
        
        DB::table('report_heading_categories')->insert($data);
    }
}
