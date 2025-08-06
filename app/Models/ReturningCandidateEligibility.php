<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturningCandidateEligibility extends Model
{
    use HasFactory;

    protected $fillable = ['returning_days'];
}
