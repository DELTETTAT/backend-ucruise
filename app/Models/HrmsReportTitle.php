<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsReportTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    public function reportTitle(){
        return $this->hasMany(HrmsReportSetting::class, 'hrms_report_title_id','id');
    }
    
}


