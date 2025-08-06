<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubUser;

class HrmsCalenderAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
               'user_id',
               'date',
               'status'
    ];



 public function employee(){
    return $this->belongsTo(SubUser::class, 'user_is');
 }


}
