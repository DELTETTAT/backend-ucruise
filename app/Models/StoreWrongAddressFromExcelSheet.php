<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubUser;

class StoreWrongAddressFromExcelSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address'
    ];


    public function user(){
        return $this->hasOne(SubUser::class,'id','user_id');
    }
}
