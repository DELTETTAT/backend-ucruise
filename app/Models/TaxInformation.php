<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxInformation extends Model
{
    use HasFactory;

    protected $fillable = [
         'pan',
         'tan',
         'tds_circle_code',
         'tax_payment_frequency',
    ];
}
