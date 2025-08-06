<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HrmsNewRole;

class HrmsNewRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
             ['name' => 'Admin View', 'status' => 1],
             ['name' => 'Manager View', 'status' => 1],
             ['name' => 'Employee View', 'status' => 1],
             ['name' => 'Team Leader View', 'status' => 1],
             ['name' => 'HR View', 'status' => 1],
             ['name' => 'Manager Not Attendance View', 'status' => 1],
        ];

        foreach ($datas as $data) {
            HrmsNewRole::firstOrCreate($data);
        }

    }
}
