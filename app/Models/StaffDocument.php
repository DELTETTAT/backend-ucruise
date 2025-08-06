<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffDocument extends Model
{
    use HasFactory;

    public function staff()
    {
        return $this->belongsTo('App\Models\User','staff_id','id');
    }
}
