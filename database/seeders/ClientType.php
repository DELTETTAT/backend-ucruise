<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

     
     
    $clientType = array(
        'Self Managed',
        'Plan Managed',
        'Ndis Managed',
        'Level 1 Aged Care',
        'Level 2 Aged Care',
        'Level 3 Aged Care',
        'Level 4 Aged Care',
        'Sil',
        );

        foreach($clientType as $key=>$value){

        \DB::table('clienttypes')->insert(['plan_name'=>$value]);

        }
    }
}
