<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReasonType;

class ReasonTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $data = [
            ['title'=>'Ride Cancel Reasons', 'status'=>1],
            ['title'=>'Leave Reasons', 'status'=>1],
            ['title'=>'Rating Reasons', 'status'=>1],
            ['title'=>'Complaint Reasons', 'status'=>1],
            ['title'=>'Shift Change Reasons', 'status'=>1],
            ['title'=>'Temp Change Reason', 'status'=>1],
        ];
        foreach ($data as $value) {
            ReasonType::firstOrCreate($value);
        }

    }
}
