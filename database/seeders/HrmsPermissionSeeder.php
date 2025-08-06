<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HrmsPermission;

class HrmsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
             ['name' => 'Employee', 'status' => 1],
             ['name' => 'Team',  'status' => 1],
             ['name' => 'Reports','status' => 1],
             ['name' => 'Payrolls','status' => 1],
             ['name' => 'Timesheet','status' => 1],
             ['name' => 'Hiring','status' => 1],
             ['name' => 'Integration','status' => 1],
             ['name' => 'System Setup','status' => 1],
             ['name' => 'Hiring Request','status' => 1],
        ];

        foreach ($datas as $data) {
            HrmsPermission::firstOrCreate($data);
        }
    }
}
