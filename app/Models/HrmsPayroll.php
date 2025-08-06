<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubUser;

class HrmsPayroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_paid_days',
        'count_of_persent',
        'date',
        'status'
    ];


    public function users(){
        return $this->belongsTo(SubUser::class, 'user_id', 'id');
    }

    public function getStatusAttribute($value){
          $status = [ 1 => 'Approved', 2 => 'Completed', 3 => 'Pending'];
          return $status[$value];
    }
}
