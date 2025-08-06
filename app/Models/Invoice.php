<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

     // Driver relationship (assuming `driver_id` relates to SubUser)
    public function driver()
    {
        return $this->belongsTo(SubUser::class, 'driver_id')->select('id', 'first_name', 'last_name', 'email', 'phone');
    }

    // Schedule relationship
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id')->select('id', 'date', 'driver_id', 'pricebook_id', 'locality', 'city','latitude','longitude');
    }

    // Pricebook relationship
    public function pricebook()
    {
        return $this->belongsTo(PriceBook::class, 'pricebook_id')->select('id', 'name', 'locality', 'address','latitude','longitude');
    }

}
