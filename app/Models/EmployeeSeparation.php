<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSeparation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'separation_type',
        'notice_served_date',
        'last_working_date',
        'reason',
        'description_of_reason',
        'salary_process',
        'good_for_rehire',
        'remarks',
    ];
}
