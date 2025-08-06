<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTableData extends Model
{
    use HasFactory;

    function priceBook() {

        return $this->belongsTo(PriceBook::class);
    }
}
