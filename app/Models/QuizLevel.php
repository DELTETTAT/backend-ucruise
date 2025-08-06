<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizLevel extends Model
{
    use HasFactory;
    protected $table = 'quiz_levels';
    protected $fillable = [
        'title',
        'status',
    ];
    public const PAGINATE = 10;

    public function hiringQuizzes()
    {
        return $this->hasMany(HiringQuiz::class);
    }
}
