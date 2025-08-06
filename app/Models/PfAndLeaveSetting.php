<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PfAndLeaveSetting extends Model
{
    use HasFactory;

    protected $casts = [
        'leave_deduction' => 'json'
    ];

    protected $fillable = [
         'pf_enabled',
         'pf_type',
         'pf_value',
         'leave_deduction_enabled',
         'leave_deduction',
         'late_day_count_enabled',
         'late_day_max',

        //  'casual_leave',
        //  'medical_leave',
        //  'paid_leave',
        //  'unpaid_leave',
        //  'maternity_leave',
        //  'paternity_leave',
        //  'bereavement_leave',
        //  'wedding_leave',
    ];
}
