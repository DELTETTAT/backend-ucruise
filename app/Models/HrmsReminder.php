<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubUser;

class HrmsReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'target',
        'title',
        'date',
        'description',
        'type',
        'status'
    ];


   public function employees(){
       return $this->belongsTo(SubUser::class, 'target', 'id');
   }

}
