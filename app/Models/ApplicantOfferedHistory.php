<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantOfferedHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'unique_id',
        'date',
        'offered_salary',
        'joining_date',
        'joining_time',
        'is_accept',
    ];

     public function  getIsAcceptAttribute($value){
         $status = [0 => 'Offered', 1 => 'Accepted', 2 => 'Not Accepted', 3 => 'reoffered'];
         return $status[$value];
     }


}
