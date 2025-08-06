<?php

namespace App\Models;
use App\Models\ReportHeading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportHeadingCategory extends Model
{
    use HasFactory;

    public function catHeadings()
    {
        return $this->hasMany(ReportHeading::class,'category_id','id');
    }
}
