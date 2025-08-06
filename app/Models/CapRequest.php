<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapRequest extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
        'from_date',
        'address',
        'latitude',
        'longitude',
        'status',
    ];

    public function user(){
        return $this->hasOne(SubUser::class,'id','user_id');
    }
}
