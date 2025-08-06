<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubUser;


class Resignation extends Model
{
    use HasFactory;

     protected $fillable = [
        'user_id',
        'date',
        'reason',
        'description',
        'status',
        'accept_or_reject_date_of_resignation',
        'notice_served_date',
        'last_working_date',
    ];

    public function user()
{
    return $this->belongsTo(SubUser::class, 'user_id');
}

}
