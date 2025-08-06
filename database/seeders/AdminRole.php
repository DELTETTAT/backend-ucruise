<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class AdminRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         
        
        $admin = Role::firstOrCreate(['name'=>'admin']);
        $user = Role::firstOrCreate(['name'=>'user']);
        $client = Role::firstOrCreate(['name'=>'client']);
        $carer = Role::firstOrCreate(['name'=>'carer']);
        $driver=Role::firstOrCreate(['name'=>'driver']);
         
        Role::firstOrCreate(['name'=>'coordinator']);
        Role::firstOrCreate(['name'=>'hr']);
        Role::firstOrCreate(['name'=>'office_support']);
        Role::firstOrCreate(['name'=>'archived_user']);
        Role::firstOrCreate(['name'=>'archived_staff']);
        Role::firstOrCreate(['name'=>'archived_client']);
        Role::firstOrCreate(['name'=>'archived_driver']);


         
    }
}
