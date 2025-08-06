<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Status::firstOrCreate(['name'=>'Waiting']);
        Status::firstOrCreate(['name'=>'Picked']);
        Status::firstOrCreate(['name'=>'Dropped']);
        Status::firstOrCreate(['name'=>'Cancelled']);
        Status::firstOrCreate(['name'=>'No-So']);
        Status::firstOrCreate(['name'=>'Ride Started']);
        Status::firstOrCreate(['name'=>'On Going']);
        Status::firstOrCreate(['name'=>'Completed']);
        Status::firstOrCreate(['name'=>'Ride Not Started']);
        Status::firstOrCreate(['name'=>'All picked']);
        Status::firstOrCreate(['name'=>'On-leave']);
        Status::firstOrCreate(['name'=>'All No-So']);
        Status::firstOrCreate(['name'=>'All Cancelled']);
        Status::firstOrCreate(['name'=>'All On-leave']);
        
    }
}
