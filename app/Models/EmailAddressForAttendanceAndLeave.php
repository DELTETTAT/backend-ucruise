<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAddressForAttendanceAndLeave extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'cc_and_main_type',
        'type',
    ];
}
