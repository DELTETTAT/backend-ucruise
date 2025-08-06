<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsAnnouncement extends Model
{
    use HasFactory;
    public const PAGINATE = 10;
    protected $fillable = [
        'send_to',
        'title',
        'description',
        'date',
        'subject',
        'status',
    ];
}
