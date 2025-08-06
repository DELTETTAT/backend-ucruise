<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReasonType extends Model
{
    use HasFactory;

    public function reasons(){
    
        return $this->hasMany(Reason::class, 'type', 'id')->select(['id', 'message', 'type']);
    }

}
