<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceBook extends Model
{
    use HasFactory;

    function priceBookData() {

        return $this->hasMany(PriceTableData::class);
    }

    public function invoices(){
        return $this->hasMany(Invoice::class, 'pricebook_id');
    }

}

