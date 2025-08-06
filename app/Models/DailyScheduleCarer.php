<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyScheduleCarer extends Model
{
    use HasFactory;
    protected $table = 'daily_schedule_carers';
    protected $fillable = ['schedule_id', 'carer_id', 'shift_type'];

    public function schedule()
    {
        return $this->belongsTo(DailySchedule::class);
    }
    public function user()
    {
        return $this->hasOne(SubUser::class,'id','carer_id');
    }
    public function carerStatus()
    {
        return $this->hasMany(ScheduleCarerStatus::class, 'schedule_carer_id', 'id');
    }
}
