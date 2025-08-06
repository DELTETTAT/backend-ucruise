<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reschedule extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->hasOne(SubUser::class,'id','user_id');
    }
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }
}
