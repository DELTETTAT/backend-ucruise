<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTeamManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_manager_id',
        'employee_id',
        'team_attendance_access',
    ];

    public function employee(){
       return $this->belongsTo(SubUser::class, 'employee_id');
    }
}
