<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCalender extends Model
{
    use HasFactory;

    protected $table = 'company_calender'; // Table name

    protected $fillable = [
        'name',
        'description',
        'date',
        'event_type',
    ];
    public const PAGINATE = 10;
}
