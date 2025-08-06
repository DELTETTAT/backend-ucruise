<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsResumeUpload extends Model
{
    use HasFactory;

    protected $table = 'hrms_resume_uploads';
    protected $fillable = [
        'id',
        'session_id',
        'resume_name',
        'is_accept',
    ];
}
