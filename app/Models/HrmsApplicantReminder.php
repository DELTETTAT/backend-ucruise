<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsApplicantReminder extends Model
{
    use HasFactory;

    public const PAGINATE = 10;

    protected $fillable = [
        'new_applicant_id',
        'title',
        'description',
        'date',
        'type',
        'hiring_template_id',
        'status',
    ];

    public function applicant(){
        return $this->belongsTo(NewApplicant::class, 'new_applicant_id', 'id');
    }

    public function template(){
        return $this->belongsTo(HiringTemplate::class, 'hiring_template_id', 'id');
    }
    

}
