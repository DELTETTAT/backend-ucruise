<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdsSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tds_from',
        'tds_to',
        'tds_type',
        'tds_value',
        'tds_enabled',
        'tds',
    ];
}
