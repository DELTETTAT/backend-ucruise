<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmsEmployeeEmail extends Model
{
    use HasFactory;

    public const PAGINATE = 15;

    protected $fillable = [
        'user_id',
        'stages',
        'email',
        'template_name',
        'email_title',
        'email_content',
        'email_image',
        'email_pdf',
        'send_date'
    ];

    public function user(){
        return $this->belongsTo(NewApplicant::class,'user_id','id')->select('id','first_name','last_name','email');
    }
}
