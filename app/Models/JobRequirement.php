<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequirement extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'employee_id',
        'email',
        'phone',
        'company_name',
        'address',
        'designation_id',
        'roles',
        'job_type',
        'no_of_required_emp',
        'gender',
        'priority',
        'status',
        'job_description',
        'justify_need',
        'qualifications',
        'benefits',
        'start_date',
        'deadline',
        'shift_schedule',
        'post_status',
        'work_type',
        'pay',

    ];

    public const PAGINATE = 10;
    
    public function Designation()  {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }
    public function quizLevel()
    {
        return $this->belongsTo(QuizLevel::class, 'roles','id');
    }
}
