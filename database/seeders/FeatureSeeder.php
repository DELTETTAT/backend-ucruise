<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $features = [
            'Customer support availability',
            'Account management for updates',
            'Discounts, promotions, or bundled packages',
            'Cancellation and refund options'

        ];

        foreach ($features as $feature) {
            DB::table('features')->insert([
                'name' => $feature,
            ]);
        }
    }
}
