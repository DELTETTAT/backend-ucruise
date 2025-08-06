<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShiftTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shift_types')->delete();
        $shiftTypes = [
            'pick',
            'pick and drop',
            'drop',
            
        ];

        foreach ($shiftTypes as $type) {
            DB::table('shift_types')->insert([
                'name' => $type,
            ]);
        }
    }
}
