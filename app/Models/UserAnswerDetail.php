<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswerDetail extends Model
{
    use HasFactory;
    protected $table = 'user_answer_detail';
    protected $fillable = [
        'new_applicant_id',
        'question_id',
        'quiz_id',
        'answer_id',
        'description',
        'question_type_id',
        'is_answer_correct',
    ];

    public function quizQuestion(){
        return $this->belongsTo(QuizQuestionDetail::class, 'question_id', 'id');
    }
    public function quizAnswer(){
        return $this->belongsTo(QuizAnswerDetail::class, 'answer_id', 'id');
    }
}
