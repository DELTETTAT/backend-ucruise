<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeSubDocument extends Model
{
    use HasFactory;

    protected $fillable = [
             'name',
             'employee_id',
             'document_title_id',
             'file',
    ];


    public function title(){
        return $this->belongsTo(HrmsEmployeeDocument::class, 'document_title_id');
    }



}
