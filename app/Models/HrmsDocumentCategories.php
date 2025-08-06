<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HrmsDocument;

class HrmsDocumentCategories extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'document_id',
        'category'
    ];

    public function documents(){
        return $this->belongsTo(HrmsDocument::class, 'id','document_id');
   }


   public function employeeDocuments()
   {
       return $this->hasMany(HrmsEmployeeDocument::class, 'sub_document_id');
   }

   


}
