<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeSalarySlab extends Model
{
    use HasFactory;

    protected $fillable = [
         'experience_level',
         'salary',
         'year_experience',
         'experience_level',
    ];
}
