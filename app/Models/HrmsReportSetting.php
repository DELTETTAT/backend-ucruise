<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsReportSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hrms_report_title_id',
        'status',
    ];

    public function reportSettings(){
        return $this->belongsTo(HrmsReportTitle::class, 'hrms_report_title_id','id');
    }
}
