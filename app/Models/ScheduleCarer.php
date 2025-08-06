<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCarer extends Model
{
    use HasFactory;
    protected $fillable = ['schedule_id', 'carer_id', 'shift_type'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
    public function user()
    {
        return $this->hasOne(SubUser::class,'id','carer_id');
    }

    public function usersdata()
    {
        return $this->hasOne(User::class,'id','carer_id');
    }
    public function carerStatus()
    {
        return $this->hasMany(ScheduleCarerStatus::class);
    }

    public function userAddress()
    {
        return $this->hasOneThrough(SubUserAddresse::class, SubUser::class, 'id', 'sub_user_id', 'carer_id','id')->latest('id');
    }
}
