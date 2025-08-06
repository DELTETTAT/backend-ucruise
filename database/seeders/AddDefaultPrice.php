<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PriceBook;
class AddDefaultPrice extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        
        $price  = new PriceBook();
        $price->name = 'DEFAULT';
        $price->status = 1;
        $price->save();
         

        \DB::table('price_table_data')->insert(array(
            array(
            'price_book_id' => $price->id,
            'day_of_week' => 'DEFAULT',
            'per_hour' =>100,
             
            ),
            ));
    }
}
