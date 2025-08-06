<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCarerComplaint extends Model
{
    use HasFactory;
    public function employee()
    {
        return $this->belongsTo(SubUser::class, 'staff_id');
    }

    public function driver()
    {
        return $this->belongsTo(SubUser::class, 'driver_id');
    }

    public function reason()
    {
        return $this->belongsTo(Reason::class, 'reason_id');
    }
}
