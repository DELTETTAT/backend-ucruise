<?php

namespace App\Models;

use App\Http\Controllers\Superadmin\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySchedule extends Model
{
    use HasFactory;
    protected $fillable = ['end_date'];

    function clients()
    {

        return $this->hasMany(ScheduleClient::class);
    }

    function shiftType()
    {

        return $this->hasOne(ShiftTypes::class, 'id', 'shift_type_id');
    }

    function driver()
    {

        return $this->hasOne(SubUser::class, 'id', 'driver_id');
    }

    function carers()
    {

        return $this->hasMany(DailyScheduleCarer::class, 'schedule_id', 'id');
    }

    function mileageClients()
    {

        return $this->hasMany(ScheduleMileageClient::class);
    }
    function scheduleStatus()
    {
        return $this->hasOne(ScheduleStatus::class, 'schedule_carer_id', 'id');
    }
    function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    function pricebook()
    {
        return $this->belongsTo(PriceBook::class);
    }

    public function schedule()
    {
        return $this->belongsTo(DailySchedule::class, 'schedule_id');
    }
}
