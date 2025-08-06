<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'notice_period_days',
        'salary_process_after_in_days',
        'clear_salary',
        'hold_one_month_salary',
        'clear_salary_after_notice',
        'salary_status_for_month_hours',
    ];
}
