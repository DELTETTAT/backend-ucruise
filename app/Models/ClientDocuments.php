<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClientDocuments extends Model
{
    protected $fillable = [
       'category',
        'type',
        'no_expireation',
        'category',
        'expire',
        'name',
        'client_id', // Add client_id to the fillable property
    ];
    use HasFactory;


    public function clients()
    {
        return $this->belongsTo('App\Models\User','client_id','id');
    }
}
