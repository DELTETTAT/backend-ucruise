<?php

namespace App\Models;

use App\Http\Controllers\Superadmin\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
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

        return $this->hasMany(ScheduleCarer::class);
    }

    function tasks()
    {

        return $this->hasMany(ScheduleTask::class);
    }

    function mileageClients()
    {

        return $this->hasMany(ScheduleMileageClient::class);
    }
    function scheduleStatus()
    {
        return $this->hasOne(ScheduleStatus::class);
    }
    function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    function pricebook()
    {
        return $this->belongsTo(PriceBook::class, 'pricebook_id','id')->select('id','name','longitude','latitude');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function invoices(){
        return $this->hasMany(Invoice::class, 'schedule_id');
    }

    public function carerStatus(){
        return $this->hasOne(ScheduleCarerStatus::class, 'schedule_carer_id');
    }

}
