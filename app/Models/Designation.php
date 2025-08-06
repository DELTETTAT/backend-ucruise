<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewApplicant;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use HasFactory;

    use softDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'title',
        'description',
        'image',
        'status'
    ];

    public const PAGINATE = 10;

    public function Candidates()  {
        return $this->hasMany(NewApplicant::class, 'designation_id', 'id');
    }

    public function JobRequiements()  {
        return $this->hasMany(JobRequirement::class, 'designation_id', 'id');
    }
    public function hiringQuizzes()
    {
        return $this->hasMany(HiringQuiz::class);
    }

}
