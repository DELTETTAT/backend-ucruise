<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    use HasFactory;
    public const PAGINATE = 10;

    protected $fillable = [
        'title',
        'status'
    ];
}
