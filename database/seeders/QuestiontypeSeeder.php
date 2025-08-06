<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionType;

class QuestiontypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            $data = [
                ['title'=>'Yes/No', 'status'=>1],
                ['title'=>'Multiple Choice', 'status'=>1],
                ['title'=>'Text Field', 'status'=>1],
                ['title'=>'Checkbox', 'status'=>1],
            ];

        foreach ($data as $value) {
            QuestionType::firstOrCreate($value);
        }
    }
}
