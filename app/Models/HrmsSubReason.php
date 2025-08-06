<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HrmsReason;

class HrmsSubReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'sub_categories',
        'reason_id'
    ];

    public function reason(){
        return $this->belongsTo(HrmsReason::class, 'reason_id', 'id');
    }

}
