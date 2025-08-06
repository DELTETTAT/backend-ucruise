<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\SubUser;

class DailyWorkReport extends Model
{
    use HasFactory;

    protected $casts = [
          'report_content' => 'array'
    ];

    protected $fillable = ['user_id','date','report_content'];

    public function users(){
        return $this->belongsTo(SubUser::class, 'user_id','id');
    }


}
