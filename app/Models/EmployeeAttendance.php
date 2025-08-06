<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\SubUser;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'date',
        'login_time',
        'logout_time',
        'ideal_time',
        'production',
        'break',
        'overtime',
        'activity_log'
    ];

    // protected $casts = [
    //     'activity_log' => 'array',
    // ];
      protected $casts = [
            'activity_log' => 'json'
      ];


    public function getLoginTimeAttribute($value){
        return Carbon::parse($value)->format("h:i A");
    }

    public function getLogoutTimeAttribute($value){
        return $value ? Carbon::parse($value)->format("h:i A") : null;
    }

    // public function getDateAttribute($value){
    //      return Carbon::parse($value)->format("d M, Y");
    // }


    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    
    public function employees(){
        return $this->belongsTo(SubUser::class, 'user_id');
    }

    

}
