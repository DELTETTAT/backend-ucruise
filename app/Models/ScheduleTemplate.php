<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    use HasFactory;
    public function pricebook()
    {
        return $this->belongsTo(PriceBook::class);
    }
}
