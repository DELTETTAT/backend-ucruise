<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewApplicant;

class NewApplicantCabAddress extends Model
{
    use HasFactory;
    protected $table = 'new_applicant_cab_addresses';
    protected $fillable = [
        'new_applicant_id',
        'cab_address',
        'longitude',
        'latitude',
        'locality',
    ];


    public function NewApplicant(){
        return $this->belongsTo(NewApplicant::class, 'new_applicant_id', 'id');
    }
}
