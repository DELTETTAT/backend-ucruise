<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteGroupSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'route_group_id',
        'date',
        'pick_time',
        'drop_time',
        'shift_finishes_next_day',
        'custom_checked',
        'infinite_checked',
        'driver_id',
        'vehicle_id',
        'shift_type_id',
        'scheduleLocation',
        'scheduleCity',
        'selectedLocationLat',
        'selectedLocationLng',
        'pricebook_id',
        'is_repeat',
        'carers',
        'repeat',
        'seats',
        'reacurrance',
        'end_date',
        'repeat_weeks',
        'occurs_on',
        'is_schedule',
        'schedule_id'
    ];

    protected $casts = [
        'carers' => 'array',
        'occurs_on' => 'array',
    ];
}
