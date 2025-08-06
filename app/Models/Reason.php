<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    use HasFactory;


    protected $fillable = [
        'message',
        'type',
    ];

    public function reasonType(){
        
        return $this->belongsTo(ReasonType::class, 'type', 'id');
    }
}


