<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewApplicant;
use App\Models\SubUser;

class UpdateApplicantHistory extends Model
{
    use HasFactory;

    protected $casts = [
        'changed_fields' => 'json'
    ];


    protected $fillable = [
        'applicant_id',
        'date',
        'time',
        'changed_fields',
        'updated_by',
        'notes',
        'changed'
    ];


    public function applicant()
    {
        return $this->belongsTo(NewApplicant::class, 'applicant_id', 'id');
    }
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }


}
