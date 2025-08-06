<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelUploadError extends Model
{
    use HasFactory;

    protected $fillable = [
            'upload_id',
            'batch_number',
            'errors',
            'status',
    ];

    
}
