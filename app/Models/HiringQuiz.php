<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HiringQuiz extends Model
{
    use HasFactory;

    public const PAGINATE = 10;

    protected $fillable = [
                    'id',
                    'name',
                    'description',
                    'questions_details',
                    'desgination_id',
                    'quiz_level_id',
                    'created_by'
    ];

    public function getDesignationDetails()
    {
        return $this->hasOne(Designation::class, "id", "desgination_id");
    }

    public function getUserDetails()
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

    public function getQuizLevel()
    {
        return $this->hasOne(QuizLevel::class, "id", "quiz_level_id");
    }

    public function getQuizQuestionDetails()
    {
        return $this->hasMany(QuizQuestionDetail::class, "hiring_quiz_id", "id");
    }
}
