<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupLoginUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'email',
        'user_id',
        'status'
    ];

    protected $casts = [
        'user_id' => 'array'
    ];
}
