<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsTimeAndShift extends Model
{
    use HasFactory;

    protected $table = 'hrms_time_and_shifts';

    protected $fillable = [
        'shift_name',
        'shift_time',
        'shift_days',
        'shift_finishs_next_day'
    ];

    protected $casts = [
        'shift_time' => 'array',
        'shift_days' => 'array',
    ];
}
