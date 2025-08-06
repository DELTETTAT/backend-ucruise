<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuizAnswerDetail;

class QuizQuestionDetail extends Model
{
    use HasFactory;

    public function getDesignationDetails()
    {
        return $this->hasOne(Designation::class, "id", "desgination_id");
    }

    public function getuserDetails()
    {
        return $this->hasOne(User::class, "id", "created_by");
    }

    public function answerDetail(){
        return $this->hasMany(QuizAnswerDetail::class, 'quiz_question_detail_id', 'id');
    }
}
