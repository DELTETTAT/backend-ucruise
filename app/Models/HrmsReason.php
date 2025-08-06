<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HrmsSubReason;

class HrmsReason extends Model
{
    use HasFactory;

    protected  $fillable = [
        'id',
        'title_of_reason'
    ];


    public function subCategories(){
        return $this->hasMany(HrmsSubReason::class, 'reason_id', 'id');
    }
}
