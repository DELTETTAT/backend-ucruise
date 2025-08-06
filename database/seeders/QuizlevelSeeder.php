<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuizLevel;
class QuizlevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
                ['title'=>'Junior Leavel', 'status'=>1],
                ['title'=>'Mid Leavel', 'status'=>1],
                ['title'=>'Senior Leavel', 'status'=>1],
        ];

        foreach ($data as $value) {
            QuizLevel::firstOrCreate($value);
        }
    }
}
