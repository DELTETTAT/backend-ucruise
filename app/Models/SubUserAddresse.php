<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUserAddresse extends Model
{

    protected $fillable = [
        'sub_user_id',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'schedule_carer_relocations_id',
    ];
    use HasFactory;
    public function carer()
    {
        return $this->belongsTo(SubUser::class);
    }

    public function user()
    {
        return $this->hasOne(SubUser::class, 'id', 'sub_user_id');
    }
}
