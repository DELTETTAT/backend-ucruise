<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'actual_days_in_month',
        'working_times_hours_in_month'
    ];

    protected $table = 'payroll_schedules';
}
