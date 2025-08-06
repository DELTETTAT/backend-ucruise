<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_title_id',
        'sub_document_id',
        'file',
    ];

    
    public function documents(){
        return $this->hasMany(HrmsEmployeeSubDocument::class, 'document_title_id');
    }

    public function documentTitle()
    {
        return $this->belongsTo(HrmsDocument::class, 'document_title_id');
    }

    // Sub-document relation
    public function subDocument()
    {
        return $this->belongsTo(HrmsDocumentCategory::class, 'sub_document_id');
    }




}
