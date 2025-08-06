<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCarerRelocation extends Model
{
    use HasFactory;
    
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

    public function user()
    {
        return $this->hasOne(SubUser::class,'id','staff_id');
    }
    
    
}
