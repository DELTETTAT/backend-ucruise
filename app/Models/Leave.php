<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $fillable = ['staff_id', 'schedule_id', 'type', 'reason_id', 'start_date', 'end_date', 'status', 'text', 'leave_type','emergency_leave', 'email_content'];

    const PAGINATE = 10;

    public function staff()
    {
        return $this->hasOne(SubUser::class,'id','staff_id');
    }
    public function reason()
    {
        return $this->belongsTo(Reason::class, 'reason_id', 'id');
    }


    public function user(){
        return $this->belongsTo(SubUser::class, 'staff_id', 'id');
    }

    public function  getIsAcceptAttribute($value){
         $status = [0 => 'Submitted', 1 => 'Accepted', 2 => 'Rejected'];
         return $status[$value];
     }
    // public function getStatusAttribute(){
    //     $values = [0 => "Submitted", 1 => "Accepted", 2 => "Rejected"];
    //     return $values[$this->attributes['status']] ?? "Unknown";
    // }


    // public function getTypeAttribute(){
    //     $values = [1 => "Full Leave", 2 => "Morning Half", 3 => "Eveing Half"];
    //     return $values[$this->attributes['type']] ?? "Unknown";
    // }

}
