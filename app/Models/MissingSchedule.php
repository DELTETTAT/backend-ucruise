<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'office_distance',
        'latitude',
        'longitude',
        'address',
        'profile_image',
        'shift_type',
        'schedule_type',
        'missing_reason',
        'date',
        'created_at',
        'updated_at'
    ];

}
