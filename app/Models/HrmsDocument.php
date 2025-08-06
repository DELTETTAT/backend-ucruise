<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HrmsDocumentCategories;

class HrmsDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title'
    ];

    public function documentCategories(){
         return $this->hasMany(HrmsDocumentCategories::class, 'document_id',);
    }

    
}
